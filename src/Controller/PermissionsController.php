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
namespace RolesCapabilities\Controller;

use Cake\Utility\Inflector;
use RolesCapabilities\Controller\AppController;

/**
 * Permissions Controller
 *
 * @property \RolesCapabilities\Model\Table\PermissionsTable $Permissions
 */
class PermissionsController extends AppController
{
    /**
     * @var allowedActions
     */
    protected $allowedActions = ['view', 'edit', 'delete'];

    /**
     * Index method
     *
     * @return \Cake\Network\Response|void
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
        $permissions = $this->paginate($this->Permissions);

        $this->set(compact('permissions'));
        $this->set('_serialize', ['permissions']);
    }

    /**
     * View method
     *
     * @param string|null $id Permission id.
     * @return \Cake\Network\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $permission = $this->Permissions->get($id);

        $this->set('permission', $permission);
        $this->set('_serialize', ['permission']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
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
        $permission = $this->Permissions->newEntity();
        if ($this->request->is('post')) {
            $permission = $this->Permissions->patchEntity($permission, $data);
            if ($this->Permissions->save($permission)) {
                $this->Flash->success(__('The  permission has been saved.'));

                return $this->redirect($this->referer());
            }
            $this->Flash->error(__('The  permission could not be saved. Please, try again.'));
        }
        $users[''] = '';
        asort($users);
        $types[''] = '';
        foreach ($this->allowedActions as $action) {
            $types[$action] = Inflector::humanize($action);
        }
        $this->set(compact('permission', 'users', 'types'));
        $this->set('_serialize', ['permission']);
    }

    /**
     * Edit method
     *
     * @param string|null $id  Permission id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $permission = $this->Permissions->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $permission = $this->Permissions->patchEntity($Permission, $this->request->getData());
            if ($this->Permissions->save($permission)) {
                $this->Flash->success(__('The  permission has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The  permission could not be saved. Please, try again.'));
        }
        $this->set(compact('permission'));
        $this->set('_serialize', ['permission']);
    }

    /**
     * Delete method
     *
     * @param string|null $id  Permission id.
     * @return \Cake\Network\Response|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $data = $this->request->getData();
        $permission = $this->Permissions->get($id);
        if ($this->Permissions->delete($permission)) {
            $this->Flash->success(__('The  permission has been deleted.'));
        } else {
            $this->Flash->error(__('The  permission could not be deleted. Please, try again.'));
        }

        $this->redirect($this->referer());
    }
}
