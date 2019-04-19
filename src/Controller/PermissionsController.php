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
use InvalidArgumentException;
use RolesCapabilities\Model\Table\PermissionsTable;

/**
 * Permissions Controller
 *
 * @property \RolesCapabilities\Model\Table\PermissionsTable $Permissions
 */
class PermissionsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
        $conditions = [];
        $params = (array)$this->request->query();
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
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id = null)
    {
        $permission = $this->Permissions->get($id);

        $this->set('permission', $permission);
        $this->set('_serialize', ['permission']);
    }

    /**
     * Add method
     *
     * @throws \InvalidArgumentException when neither 'group_id', nor 'user_id' are present in request data
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $data = (array)$this->request->getData();
        if (!empty($data['group_id'])) {
            $data['owner_model'] = 'Groups';
            $data['owner_foreign_key'] = $data['group_id'];
        } elseif (!empty($data['user_id'])) {
            $data['owner_model'] = 'Users';
            $data['owner_foreign_key'] = $data['user_id'];
        } else {
            throw new InvalidArgumentException("Missing user or group!");
        }

        $data['creator'] = $this->Auth->user('id');
        $permission = $this->Permissions->newEntity();
        if ($this->request->is('post')) {
            $permission = $this->Permissions->patchEntity($permission, $data);
            $this->Permissions->save($permission) ?
                $this->Flash->success((string)__('The  permission has been saved.')) :
                $this->Flash->error((string)__('The  permission could not be saved. Please, try again.'));

            return $this->redirect($this->referer());
        }
        $users[''] = '';
        asort($users);
        $types[''] = '';
        foreach (PermissionsTable::ALLOWED_ACTIONS as $action) {
            $types[$action] = Inflector::humanize($action);
        }
        $this->set(compact('permission', 'users', 'types'));
        $this->set('_serialize', ['permission']);
    }

    /**
     * Edit method
     *
     * @param string|null $id  Permission id.
     * @return \Cake\Http\Response|void|null Redirects on successful edit, renders view otherwise.
     */
    public function edit(string $id = null)
    {
        $permission = $this->Permissions->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $permission = $this->Permissions->patchEntity($permission, (array)$this->request->getData());
            $this->Permissions->save($permission) ?
                $this->Flash->success((string)__('The  permission has been saved.')) :
                $this->Flash->error((string)__('The  permission could not be saved. Please, try again.'));

            return $this->redirect(['action' => 'index']);
        }
        $this->set(compact('permission'));
        $this->set('_serialize', ['permission']);
    }

    /**
     * Delete method
     *
     * @param string|null $id  Permission id.
     * @return \Cake\Http\Response|void|null Redirects to index.
     */
    public function delete(string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $data = $this->request->getData();
        $permission = $this->Permissions->get($id);
        if ($this->Permissions->delete($permission)) {
            $this->Flash->success((string)__('The  permission has been deleted.'));
        } else {
            $this->Flash->error((string)__('The  permission could not be deleted. Please, try again.'));
        }

        $this->redirect($this->referer());
    }
}
