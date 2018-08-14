<?php
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
namespace RolesCapabilities;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Access\CapabilitiesAccess;
use RolesCapabilities\Access\Utils;

/**
 * This class is responsible for filtering all application queries
 * by applying current user capabilities and permissions.
 */
final class FilterQuery
{
    /**
     * Query instance.
     *
     * @var \Cake\Datasource\QueryInterface
     */
    private $query;

    /**
     * Current user and action capabilities.
     *
     * @var array
     */
    private $capabilities = [];

    /**
     * Query target table.
     *
     * @var \Cake\Datasource\RepositoryInterface
     */
    private $table;

    /**
     * Current user info.
     *
     * @var array
     */
    private $user;

    /**
     * Association types supported for parent join functionality.
     *
     * @var array
     */
    private $parentJoinAssocations = ['manyToMany', 'manyToOne'];

    /**
     * Filterable flag
     *
     * False by default and only set to true if specific conditions apply.
     *
     * @var bool
     */
    private $filterable = false;

    /**
     * [execute description]
     *
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @return void
     */
    public function __construct(QueryInterface $query, RepositoryInterface $table, array $user = [])
    {
        $this->query = $query;
        $this->table = $table;
        $this->user = $user;

        if (empty($user) || $this->isSuperuser() || $this->isSkipTable()) {
            return;
        }

        $controller = App::className(
            App::shortName(get_class($this->table), 'Model/Table', 'Table') . 'Controller',
            'Controller'
        );
        if (! $controller) {
            return;
        }

        $this->capabilities = [
            // get current user capabilities
            'user' => Utils::fetchUserCapabilities($user['id']),
            // @todo currently we are always assume index action, this probably needs to change in the future
            'action' => Utils::getCapabilities($controller, ['index'])
        ];

        // flag query as filterable
        $this->filterable = true;
    }

    /**
     * Validates if provided user is a superuser.
     *
     * @return bool
     */
    private function isSuperuser()
    {
        if (! isset($this->user['is_superuser'])) {
            return false;
        }

        return (bool)$this->user['is_superuser'];
    }

    /**
     * Validates if provided user is supervisor.
     *
     * @return bool
     */
    private function isSupervisor()
    {
        if (! isset($this->user['is_supervisor'])) {
            return false;
        }

        return (bool)$this->user['is_supervisor'];
    }

    /**
     *  Validates if provided table must be skipped from access checks.
     *
     * @return bool
     */
    private function isSkipTable()
    {
        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byInstance');
        if (in_array(get_class($this->table), $config)) {
            return true;
        }

        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byRegistryAlias');
        if (in_array($this->table->getRegistryAlias(), $config)) {
            return true;
        }

        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byTableName');
        if (in_array($this->table->getTable(), $config)) {
            return true;
        }

        return false;
    }

    /**
     * Executes Query filtering functionality.
     *
     * @return \Cake\Datasource\QueryInterface
     */
    public function execute()
    {
        // query is not filterable, return it as is.
        if (! $this->filterable) {
            return $this->query;
        }

        if ($this->hasFullAccess()) {
            return $this->query;
        }

        $where = $this->filterQuery($this->user);

        if (empty($where)) {
            // if user has neither owner nor full capability on current action then filter out all records
            $this->query->where([$this->table->aliasField($this->table->primaryKey()) => null]);

            return $this->query;
        }

        $this->query->where($where);

        return $this->query;
    }

    /**
     *
     * Filter query results by applying where clause conditions
     * based on current user capabilities.
     *
     * If current user has limited access to index or view records
     * only assigned to him, then the appropriate condition will
     * be applied to the sql where clause.
     *
     * @param array $user User info
     * @return bool
     */
    private function filterQuery(array $user)
    {
        $result = array_merge($this->getOwnerFields($user), $this->getPermissions($user));

        $joins = $this->getParentJoins($user);
        if (! empty($joins)) {
            foreach ($joins as $name => $conditions) {
                $result = array_merge($result, $conditions);
                // disable access check on joins
                $this->query->leftJoinWith($name, function ($q) {
                    return $q->applyOptions(['accessCheck' => false]);
                });
            }
        }

        // check supervisor access
        if ($this->isSupervisor()) {
            foreach (Utils::getReportToUsers($this->user['id']) as $user) {
                $this->user = $user;
                $result = array_merge_recursive($result, $this->filterQuery($user->toArray()));
            }
        }

        return $result;
    }

    /**
     * Check if user has full access.
     *
     * @return bool
     */
    private function hasFullAccess()
    {
        if (! isset($this->capabilities['action'][Utils::getTypeFull()])) {
            return false;
        }

        // check user capabilities against action's full capabilities
        foreach ($this->capabilities['action'][Utils::getTypeFull()] as $capability) {
            // if current action's full capability is matched in user's capabilities just return
            if (in_array($capability->getName(), $this->capabilities['user'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return owner fields and value.
     *
     * @param array $user User info
     * @return array
     */
    private function getOwnerFields(array $user)
    {
        if (! isset($this->capabilities['action'][Utils::getTypeOwner()])) {
            return [];
        }

        $result = [];
        // check user capabilities against action's owner capabilities
        foreach ($this->capabilities['action'][Utils::getTypeOwner()] as $capability) {
            if (in_array($capability->getName(), $this->capabilities['user'])) {
                // if user has owner capability for current action add appropriate conditions to where clause
                $result[$this->table->aliasField($capability->getField()) . ' IN'] = $user['id'];
            }
        }

        return $result;
    }

    /**
     * Return permissions.
     *
     * @param array $user User info
     * @return array
     */
    private function getPermissions(array $user)
    {
        $groups = TableRegistry::getTableLocator()->get('RolesCapabilities.Capabilities')
            ->getUserGroups($user['id']);

        $query = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions')
            ->find('all')
            ->select('foreign_key')
            ->where([
                // WARNING: this might conflict with APP table's name matching a plugin's table name
                'model' => $this->table->getAlias(),
                'type IN ' => ['view'],
                'OR' => [
                    ['owner_foreign_key IN ' => array_keys($groups), 'owner_model' => 'Groups'],
                    ['owner_foreign_key' => $user['id'], 'owner_model' => 'Users']
                ]
            ])
            ->applyOptions(['accessCheck' => false]);

        if ($query->isEmpty()) {
            return [];
        }

        $values = [];
        foreach ($query->all() as $permission) {
            $values[] = $permission->foreign_key;
        }

        return [$this->table->aliasField($this->table->getPrimaryKey()) . ' IN' => array_unique($values)];
    }

    /**
     * Return parent association joins.
     *
     * @param array $user User info
     * @return array
     */
    private function getParentJoins(array $user)
    {
        if (! isset($this->capabilities['action'][Utils::getTypeParent()])) {
            return [];
        }

        $modules = [];
        foreach ($this->capabilities['action'][Utils::getTypeParent()] as $capability) {
            if (in_array($capability->getName(), $this->capabilities['user'])) {
                $modules = $capability->getParentModules();
            }
        }

        if (empty($modules)) {
            return [];
        }

        $result = [];
        foreach ($this->table->associations() as $association) {
            $conditions = $this->getParentJoin($association, $user, $modules);
            if (empty($conditions)) {
                continue;
            }

            $result[$association->getName()] = $conditions;
        }

        return $result;
    }

    /**
     * Return parent association join.
     *
     * @param \Cake\ORM\Association $association Association instance
     * @param array $user User info
     * @param array $modules Parent modules
     * @return array
     */
    private function getParentJoin(Association $association, array $user, array $modules)
    {
        if (! in_array($association->type(), $this->parentJoinAssocations)) {
            return [];
        }

        $targetTable = $association->getTarget();
        $targetName = App::shortName(get_class($targetTable), 'Model/Table', 'Table');
        if (! in_array($targetName, $modules)) {
            return [];
        }

        $result = [];
        foreach (Utils::getTableAssignationFields($targetTable) as $field) {
            $result[$targetTable->aliasField($field)] = $user['id'];
        }

        return $result;
    }
}
