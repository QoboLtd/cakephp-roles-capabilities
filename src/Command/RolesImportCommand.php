<?php
declare(strict_types=1);

/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace RolesCapabilities\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Webmozart\Assert\Assert;

/**
 * Import Task
 *
 * Import system roles.
 */
class RolesImportCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser
            ->setDescription('Imports roles from configuration to the database.');

        return $parser;
    }

    /**
     * @inheritdoc
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $io->info('Import system roles');
        $io->hr();

        $roles = Configure::read('RolesCapabilities.Roles');
        if (empty($roles)) {
            $io->warning("No roles configured for importing.  Nothing to do.");

            return 1;
        }

        /**
         * @var \RolesCapabilities\Model\Table\RolesTable $table
         */
        $table = $this->getTableLocator()->get('RolesCapabilities.Roles');

        foreach ($roles as $role) {
            if (empty($role['name'])) {
                $io->warning("Skipping role without a name.");
                continue;
            }

            $query = $table->find()->where(['name' => $role['name']])->contain(['Groups' => function ($q) {
                return $q->select(['Groups.id']);
            }]);

            Assert::isInstanceOf($query, \Cake\ORM\Query::class);

            $entity = $query->first();

            Assert::nullOrIsInstanceOf($entity, EntityInterface::class);

            $linkedGroups = null === $entity ? [] : array_map(function ($item) {
                return $item->get('id');
            }, $entity->get('groups'));

            if (null !== $entity && $entity->get('deny_edit')) {
                $io->warning(sprintf('Roles "%s" already exists and is not allowed to be modified.', $role['name']));
                continue;
            }

            null === $entity ?
                $io->info(sprintf('Creating role "%s".', $role['name'])) :
                $io->info(sprintf('Updating role "%s".', $role['name']));

            $entity = null === $entity ? $table->newEntity() : $entity;
            $entity = $table->patchEntity($entity, $role);

            if (! $table->save($entity)) {
                $io->error("Errors: \n" . implode("\n", $this->getImportErrors($entity)));
                $io->abort("Failed to create role [" . $entity->get('name') . "]");
            }

            $group = $this->getGroupByRoleName($entity->get('name'));

            if (null === $group || in_array($group->get('id'), $linkedGroups, true)) {
                continue;
            }

            // associate imported role with matching group
            if ($table->Groups->link($entity, [$group])) {
                $io->info(sprintf('Role "%s" linked with group "%s"', $entity->get('name'), $group->get('name')));
            }
        }

        $io->success('System roles imported successfully');

        return 0;
    }

    /**
     * Fetch group entity based on role name
     *
     * @param  string $name Role name
     * @return \Cake\Datasource\EntityInterface|null
     */
    protected function getGroupByRoleName(string $name): ?EntityInterface
    {
        $result = $this->getTableLocator()->get('Groups.Groups')->find()
            ->enableHydration(true)
            ->where(['name' => $name])
            ->first();

        Assert::nullOrIsInstanceOf($result, EntityInterface::class);

        return $result;
    }

    /**
     * Get import errors from entity object.
     *
     * @param  \Cake\Datasource\EntityInterface $entity Entity instance
     * @return mixed[]
     */
    protected function getImportErrors(EntityInterface $entity): array
    {
        $result = [];

        if (empty($entity->getErrors())) {
            return $result;
        }

        foreach ($entity->getErrors() as $field => $error) {
            if (is_array($error)) {
                $msg = implode(', ', $error);
            } else {
                $msg = $error;
            }
            $result[] = $msg . ' [' . $field . ']';
        }

        return $result;
    }
}
