<?php
namespace RolesCapabilities\Controller;

use RolesCapabilities\Controller\AppController;

/**
 * Capabilities Controller
 *
 * @property \RolesCapabilities\Model\Table\CapabilitiesTable $Capabilities
 */
class CapabilitiesController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('capabilities', $this->paginate($this->Capabilities));
        $this->set('_serialize', ['capabilities']);
    }

    /**
     * View method
     *
     * @param string|null $id Capability id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $capability = $this->Capabilities->get($id, [
            'contain' => ['Roles']
        ]);
        $this->set('capability', $capability);
        $this->set('_serialize', ['capability']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $capability = $this->Capabilities->newEntity();
        if ($this->request->is('post')) {
            $capability = $this->Capabilities->patchEntity($capability, $this->request->data);
            if ($this->Capabilities->save($capability)) {
                $this->Flash->success(__('The capability has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The capability could not be saved. Please, try again.'));
            }
        }
        $roles = $this->Capabilities->Roles->find('list', ['limit' => 200]);
        $this->set(compact('capability', 'roles'));
        $this->set('_serialize', ['capability']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Capability id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $capability = $this->Capabilities->get($id, [
            'contain' => ['Roles']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $capability = $this->Capabilities->patchEntity($capability, $this->request->data);
            if ($this->Capabilities->save($capability)) {
                $this->Flash->success(__('The capability has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The capability could not be saved. Please, try again.'));
            }
        }
        $roles = $this->Capabilities->Roles->find('list', ['limit' => 200]);
        $this->set(compact('capability', 'roles'));
        $this->set('_serialize', ['capability']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Capability id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $capability = $this->Capabilities->get($id);
        if ($this->Capabilities->delete($capability)) {
            $this->Flash->success(__('The capability has been deleted.'));
        } else {
            $this->Flash->error(__('The capability could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
