<?php
namespace RolesCapabilities\Controller;

use Cake\Utility\Inflector;
use RolesCapabilities\Controller\AppController;

/**
 * PersonalPermissions Controller
 *
 * @property \RolesCapabilities\Model\Table\PersonalPermissionsTable $PersonalPermissions
 */
class PersonalPermissionsController extends AppController
{
    /**
     * @var allowedActions
     */
    protected $allowedActions = ['view', 'edit', 'delete', 'index'];

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $conditions = [];
        $params = $this->request->query();
        if (!empty($params['model'])) {
            $conditions['model'] = $params['model'];
        }
        if (!empty($params['foreign_key'])) {
            $conditions['foreign_key'] = $params['foreign_key'];
        }

        $this->paginate = [
            'conditions' => $conditions,
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
        $personalPermission = $this->PersonalPermissions->get($id);

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
        $data = $this->request->getData();
        if (!empty($data['group_id'])) {
            $data['owner_model'] = 'Groups';
            $data['owner_foreign_key'] = $data['group_id'];
        } elseif (!empty($data['user_id'])) {
            $data['owner_model'] = 'Users';
            $data['owner_foreign_key'] = $data['user_id'];
        } else {
            throw new \InvalidArgumentException("Missing user or group!");
        }

        $data['creator'] = $this->Auth->user('id');
        $personalPermission = $this->PersonalPermissions->newEntity();
        if ($this->request->is('post')) {
            $personalPermission = $this->PersonalPermissions->patchEntity($personalPermission, $data);
            if ($this->PersonalPermissions->save($personalPermission)) {
                $this->Flash->success(__('The personal permission has been saved.'));

                return $this->redirect(['plugin' => false, 'controller' => $data['model'], 'action' => 'view', $data['foreign_key']]);
            }
            $this->Flash->error(__('The personal permission could not be saved. Please, try again.'));
        }
        $users[''] = '';
        asort($users);
        $types[''] = '';
        foreach ($this->allowedActions as $action) {
            $types[$action] = Inflector::humanize($action);
        }
        $this->set(compact('personalPermission', 'users', 'types'));
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
        $this->set(compact('personalPermission'));
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
        $data = $this->request->getData();
        $personalPermission = $this->PersonalPermissions->get($id);
        if ($this->PersonalPermissions->delete($personalPermission)) {
            $this->Flash->success(__('The personal permission has been deleted.'));
        } else {
            $this->Flash->error(__('The personal permission could not be deleted. Please, try again.'));
        }

        return !empty($data['model']) && !empty($data['foreign_key']) ?
                $this->redirect(['plugin' => false, 'controller' => $data['model'], 'action' => 'view', $data['foreign_key']]) :
                $this->redirect(['action' => 'index']);
    }
}
