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

use RolesCapabilities\EntityAccess\CapabilitiesUtil;

/**
 * Roles Controller
 *
 * @property \RolesCapabilities\Model\Table\RolesTable $Roles
 * @property \RolesCapabilities\Model\Table\ExtendedCapabilitiesTable $ExtendedCapabilities
 */
class RolesController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->ExtendedCapabilities = $this->loadModel('RolesCapabilities.ExtendedCapabilities');
    }

    /**
     * Gets all capabilities
     */
    private function getAllCapabilities(): array
    {
        return CapabilitiesUtil::getAllCapabilities();
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

        $capabilities = $this->getAllCapabilities();

        $this->set('capabilities', $capabilities);
        $this->set('roleCaps', $roleCaps);
        $this->set('role', $role);
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
            $data = (array)$this->request->getData();
            /**
             * @var \RolesCapabilities\Model\Entity\Role $role
             */
            $role = $this->Roles->patchEntity($role, $data);

            if (!empty($data['capabilities'])) {
                $role->capabilities = $this->ExtendedCapabilities->newEntities(
                    json_decode($data['capabilities'], true)
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

        $capabilities = $this->getAllCapabilities();

        $this->set(compact('role', 'groups', 'capabilities'));
        $this->set('_serialize', ['role']);
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
            $data = (array)$this->request->getData();
            /**
             * @var \RolesCapabilities\Model\Entity\Role
             */
            $role = $this->Roles->patchEntity($role, $data);

            // delete existing role capabilities
            $this->ExtendedCapabilities->deleteAll(['role_id' => $id]);
            if (!empty($data['capabilities'])) {
                $role->capabilities = $this->ExtendedCapabilities->newEntities(
                    json_decode($data['capabilities'], true)
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
        // fetch role capabilities
        $roleCaps = $this->ExtendedCapabilities->find('list')->where(['role_id' => $id])->toArray();

        $capabilities = $this->getAllCapabilities();

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
