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
namespace RolesCapabilities\Controller;

use Cake\ORM\TableRegistry;
use RolesCapabilities\EntityAccess\CapabilitiesUtil;
use RolesCapabilities\Model\Table\ExtendedCapabilitiesTable;
use Webmozart\Assert\Assert;

/**
 * Roles Controller
 *
 * @property \RolesCapabilities\Model\Table\RolesTable $Roles
 * @property ExtendedCapabilitiesTable $ExtendedCapabilities
 */
class RolesController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $extendedCapabilities = $this->loadModel('RolesCapabilities.ExtendedCapabilities');
        Assert::isInstanceOf($extendedCapabilities, ExtendedCapabilitiesTable::class);

        $this->ExtendedCapabilities = $extendedCapabilities;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
        $this->set('roles', $this->paginate($this->Roles, ['contain' => 'Groups', 'maxLimit' => 500, 'limit' => 500]));
        $this->set('_serialize', ['roles']);
    }

    /**
     * View method
     *
     * @param string|null $id Role id.
     * @return \Cake\Http\Response|void|null
     */
    public function view(string $id = null)
    {
        $role = $this->Roles->get($id, [
            'contain' => ['Groups'],
        ]);

        $roleCaps = $this->ExtendedCapabilities->find('all')->where(['role_id' => $id])->toArray();
        $capabilities = CapabilitiesUtil::getAllCapabilities();

        $this->set(compact('role', 'capabilities', 'roleCaps'));
        $this->set('_serialize', ['role']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $role = $this->Roles->newEntity();
        if ($this->request->is('post')) {
            $data = $this->getRequestData();
            /**
             * @var \RolesCapabilities\Model\Entity\Role $role
             */
            $role = $this->Roles->patchEntity($role, $data);

            if (!empty($data['capabilities'])) {
                $role->capabilities = $this->ExtendedCapabilities->newEntities(
                    $data['capabilities']
                );
            }

            if ($this->Roles->save($role)) {
                $this->Flash->success((string)__d('Qobo/RolesCapabilities', 'The role has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error((string)__d('Qobo/RolesCapabilities', 'The role could not be saved. Please, try again.'));
            }
        }
        $groups = $this->Roles->Groups->find('list', ['limit' => 200]);

        $capabilities = CapabilitiesUtil::getAllCapabilities();

        $this->set(compact('role', 'groups', 'capabilities'));
        $this->set('_serialize', ['role']);
    }

    private function getRequestData(): array
    {
        $data = (array)$this->request->getData();

        // Convert capabilities from json
        if (!empty($data['capabilities'])) {
            $capabilities = json_decode($data['capabilities'], true);
            if ($capabilities === false) {
                $capabilities = [];
            }

            $locator = TableRegistry::getTableLocator();

            $data['extended_capabilities'] = [];

            foreach ($capabilities as $cap) {
                $fields = explode('@', $cap);

                $tableName = $fields[0];
                $operation = $fields[1];
                $associationName = $fields[2];

                $table = $locator->get($tableName);

                $behavior = $table->getBehavior('Authorized');
                $associations = $behavior->getAssociations();

                $capability = array_merge(
                    [
                        'resource' => $table->getRegistryAlias(),
                        'operation' => $operation,
                    ],
                    $associations[$associationName],
                );

                $data['extended_capabilities'][] = $capability;
            }
        }

        return $data;
    }

    /**
     * Edit method
     *
     * @param string|null $id Role id.
     * @return \Cake\Http\Response|void|null Redirects on successful edit, renders view otherwise.
     */
    public function edit(string $id = null)
    {
        $role = $this->Roles->get($id, [
            'contain' => ['Groups'],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->getRequestData();

            // delete existing role capabilities
            $this->ExtendedCapabilities->deleteAll(['role_id' => $id]);

            /**
             * @var \RolesCapabilities\Model\Entity\Role
             */
            $role = $this->Roles->patchEntity($role, $data);

            if ($this->Roles->save($role)) {
                $this->Flash->success((string)__d('Qobo/RolesCapabilities', 'The role has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error((string)__d('Qobo/RolesCapabilities', 'The role could not be saved. Please, try again.'));
            }
        }
        $groups = $this->Roles->Groups->find('list', ['limit' => 200]);
        // fetch role capabilities
        $roleCaps = $this->ExtendedCapabilities->find('list')->where(['role_id' => $id])->toArray();

        $capabilities = CapabilitiesUtil::getAllCapabilities();

        $this->set(compact('role', 'groups', 'capabilities', 'roleCaps'));
        $this->set('_serialize', ['role']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Role id.
     * @return \Cake\Http\Response|void|null Redirects to index.
     */
    public function delete(string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $role = $this->Roles->get($id);
        if ($this->Roles->delete($role)) {
            $this->Flash->success((string)__d('Qobo/RolesCapabilities', 'The role has been deleted.'));
        } else {
            $this->Flash->error((string)__d('Qobo/RolesCapabilities', 'The role could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
