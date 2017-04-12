<?php
namespace RolesCapabilities\Controller;

use RolesCapabilities\Controller\AppController;

/**
 * PersonalPermissions Controller
 *
 * @property \RolesCapabilities\Model\Table\PersonalPermissionsTable $PersonalPermissions
 */
class PersonalPermissionsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users']
        ];
        $personalPermissions = $this->paginate($this->PersonalPermissions);

        $this->set(compact('personalPermissions'));
        $this->set('_serialize', ['personalPermissions']);
    }

    /**
     * View method
     *
     * @param string|null $id Personal Permission id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $personalPermission = $this->PersonalPermissions->get($id, [
            'contain' => ['Users']
        ]);

        $this->set('personalPermission', $personalPermission);
        $this->set('_serialize', ['personalPermission']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $personalPermission = $this->PersonalPermissions->newEntity();
        if ($this->request->is('post')) {
            $personalPermission = $this->PersonalPermissions->patchEntity($personalPermission, $this->request->getData());
            if ($this->PersonalPermissions->save($personalPermission)) {
                $this->Flash->success(__('The personal permission has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The personal permission could not be saved. Please, try again.'));
        }
        $users = $this->PersonalPermissions->Users->find('list', ['limit' => 200]);
        $this->set(compact('personalPermission', 'users'));
        $this->set('_serialize', ['personalPermission']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Personal Permission id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $personalPermission = $this->PersonalPermissions->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $personalPermission = $this->PersonalPermissions->patchEntity($personalPermission, $this->request->getData());
            if ($this->PersonalPermissions->save($personalPermission)) {
                $this->Flash->success(__('The personal permission has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The personal permission could not be saved. Please, try again.'));
        }
        $users = $this->PersonalPermissions->Users->find('list', ['limit' => 200]);
        $this->set(compact('personalPermission', 'users'));
        $this->set('_serialize', ['personalPermission']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Personal Permission id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $personalPermission = $this->PersonalPermissions->get($id);
        if ($this->PersonalPermissions->delete($personalPermission)) {
            $this->Flash->success(__('The personal permission has been deleted.'));
        } else {
            $this->Flash->error(__('The personal permission could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
