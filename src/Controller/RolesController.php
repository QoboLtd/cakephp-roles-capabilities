<?php
namespace RolesCapabilities\Controller;

use RolesCapabilities\Controller\AppController;

/**
 * Roles Controller
 *
 * @property \RolesCapabilities\Model\Table\RolesTable $Roles
 */
class RolesController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('roles', $this->paginate($this->Roles));
        $this->set('_serialize', ['roles']);
    }

    /**
     * View method
     *
     * @param string|null $id Role id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $role = $this->Roles->get($id, [
            'contain' => ['Groups', 'Capabilities']
        ]);
        $this->set('role', $role);
        $this->set('_serialize', ['role']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $role = $this->Roles->newEntity();
        if ($this->request->is('post')) {
            $role = $this->Roles->patchEntity($role, $this->request->data);
            // prepare associated capability records for creation
            if (!empty($this->request->data['capabilities']['_names'])) {
                $role->capabilities = $this->Roles->prepareCapabilities(
                    $this->request->data['capabilities']['_names']
                );
            }
            if ($this->Roles->save($role)) {
                $this->Flash->success(__('The role has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The role could not be saved. Please, try again.'));
            }
        }
        $groups = $this->Roles->Groups->find('list', ['limit' => 200]);
        $capabilities = $this->Capability->getAllCapabilities();
        $this->set(compact('role', 'groups', 'capabilities'));
        $this->set('_serialize', ['role']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Role id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $role = $this->Roles->get($id, [
            'contain' => ['Groups']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $role = $this->Roles->patchEntity($role, $this->request->data);
            // prepare associated capability records for creation
            if (!empty($this->request->data['capabilities']['_names'])) {
                $role->capabilities = $this->Roles->prepareCapabilities(
                    $this->request->data['capabilities']['_names']
                );
            }
            // delete existing role capabilities
            $this->Roles->Capabilities->deleteAll(['role_id' => $id]);

            if ($this->Roles->save($role)) {
                $this->Flash->success(__('The role has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The role could not be saved. Please, try again.'));
            }
        }
        $groups = $this->Roles->Groups->find('list', ['limit' => 200]);
        // fetch role capabilities
        $roleCaps = $this->Roles->Capabilities->find('list')->where(['role_id' => $id])->toArray();
        $capabilities = $this->Capability->getAllCapabilities();
        $this->set(compact('role', 'groups', 'capabilities', 'roleCaps'));
        $this->set('_serialize', ['role']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Role id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $role = $this->Roles->get($id);
        if ($this->Roles->delete($role)) {
            $this->Flash->success(__('The role has been deleted.'));
        } else {
            $this->Flash->error(__('The role could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
