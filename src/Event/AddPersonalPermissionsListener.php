<?php

namespace RolesCapabilities\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class AddPersonalPermissionsListener implements EventListenerInterface
{
    protected $allowedActions = [
        'view', 'edit', 'delete'
    ];

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.View.topMenu.beforeRender' => 'addPersonalPermissionsButton',
            //'View.View.Body.Bottom' => 'addPersonalPermissionsModal',
        ];
    }

    /**
     *  addPersonalPermissionsButton method
     *
     * @param Cake\Event\Event $event of the current request
     * @param array $menu of the view page.
     * @param array $user currently logged in.
     * @return void
     */
    public function addPersonalPermissionsButton(Event $event, array $menu, array $user)
    {
        $content = $this->_addButton($event, $menu);
        $content .= $this->_addModalWindow($event, $menu);

        $event->result = $content;
    }

    /**
     *  addPersonalPermissionsModal method
     *
     * @param Cake\Event\Event $event of the current request
     * @param array $options of the view page.
     * @return void
     */
    public function addPersonalPermissionsModal(Event $event, $options)
    {
        $content = $this->_addJSHandler($event);
        debug($content);
        $event->result = $content;
    }

    /**
     *  _addButton method
     *
     * @param Cake\Event\Event $event of the current request
     * @return string   code of the button
     */
    protected function _addButton(Event $event)
    {
        return $event->subject()->Html->link(
            '<i class="fa fa-shield"></i>&nbsp;' . __('Permissions'),
            '/roles-capabilities/personal-permissions/add',
            [
                'class' => 'btn btn-default',
                'data-toggle' => "modal",
                'data-target' => "#permissions-modal-add",
                'escape' => false,
            ]
        );
    }

    /**
     *  _addDropdownButton method
     *
     * @param Cake\Event\Event $event of the current request
     * @return string   code of button
     */
    protected function _addDropdownButton(Event $event, array $menu)
    {
        $viewUrl = '/roles-capabilities/personal-permissions/indexModal';
        $addUrl = '/roles-capabilities/personal-permissions/addNew';
        if (!empty($menu[0]['url']['controller']) && !empty($menu[0]['url'][0])) {
            $viewUrl .= '?model=' . $menu[0]['url']['controller'] . '&foreign_key=' . $menu[0]['url'][0];
        }

        $button = [];

        $button[] = '<div class="btn-group btn-group-sm">';
        $button[] = '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $button[] = '<i class="fa fa-shield"></i>' . __('Permissions') . '<span class="caret"></span>';
        $button[] = '<span class="sr-only">Toggle Dropdown</span>';
        $button[] = '</button>';
        $button[] = '<ul class="dropdown-menu">';
        $button[] = '<li><a href="' . $addUrl . '" data-toggle="modal" data-target="#permissions-modal-add">Add Permission</a></li>';
        $button[] = '<li><a href="' . $viewUrl . '" data-toggle="modal" data-target="#permissions-modal-view">View Permissions</a></li>';
        $button[] = '</ul>';
        $button[] = '</div>';

        return implode("\n", $button);
    }

    /**
     *  _addModalWindow method
     *
     * @param Cake\Event\Event $event of the current request
     * @return string   code of modal window
     */
    protected function _addModalWindow(Event $event, array $menu)
    {
        $controllerName = $event->subject()->request->params['controller'];

        $users = $this->_getListOfUsers();

        $groups = $this->_getListOfGroups();

        $actions = $this->_getListOfActions();

        $permissions = $this->_getListOfPermissions($menu[0]['url']['controller'], $menu[0]['url'][0]);

        $postContent = [];
        $postContent[] = '<div class="modal fade" id="permissions-modal-add" tabindex="-1" role="dialog" aria-labelledby="mySetsLabel">';
        $postContent[] = '<div class="modal-dialog" role="document">';
        $postContent[] = '<div class="modal-content">';
        $postContent[] = '<div class="modal-header">';
        $postContent[] = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $postContent[] = '<h4 class="modal-title" id="mySetsLabel">' . __('Add Permissions') . '</h4>';
        $postContent[] = '</div>'; // modal-header
        $postContent[] = '<div class="modal-body">';
        $postContent[] = $event->subject()->Form->create('RolesCapabilities.PersonalPermissions', ['url' => '/roles-capabilities/personal-permissions/add', 'id' => 'modal-form-permissions-add']);
        $postContent[] = '<div class="sets-feedback-container"></div>';
        $postContent[] = $event->subject()->Form->hidden('foreign_key', ['value' => $event->subject()->request->params['pass'][0]]);
        $postContent[] = $event->subject()->Form->hidden('model', ['value' => $event->subject()->request->params['controller']]);
        $postContent[] = '<div class="row"><div class="col-xs-6">';
        $postContent[] = $event->subject()->Form->label(__('User'));
        $postContent[] = $event->subject()->Form->select('user_id', $users, ['class' => 'select2', 'multiple' => false, 'required' => false]);
        $postContent[] = '</div><div class="col-xs-6">';
        $postContent[] = $event->subject()->Form->label(__('Groups'));
        $postContent[] = $event->subject()->Form->select('group_id', $groups, ['class' => 'select2', 'multiple' => false, 'required' => false]);
        $postContent[] = '</div></div>';

        $postContent[] = '<div class="row"><div class="col-xs-12 col-md-12">';
        $postContent[] = $event->subject()->Form->label(__('Permission'));
        $postContent[] = $event->subject()->Form->select('type', $actions, ['class' => 'select2', 'multiple' => false, 'required' => true]);
        $postContent[] = '</div></div>';

        $postContent[] = '<hr/><div class="row"><div class="col-xs-10">&nbsp;</div><div class="col-xs-2">';
        $postContent[] = $event->subject()->Form->button(__('Submit'), ['name' => 'btn_operation', 'value' => 'submit', 'class' => 'btn btn-primary']);
        $postContent[] = $event->subject()->Form->end();
        $postContent[] = '</div></div><hr/>';

        $postContent[] = $this->_showExistingPermissions($permissions, $event);

        $postContent[] = '</div>'; //modal-body
        $postContent[] = '<div class="modal-footer">';
        $postContent[] = '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $postContent[] = '</div>';
        $postContent[] = '</div>'; // modal-content
        $postContent[] = '</div>'; //modal-dialog
        $postContent[] = '</div>'; // modal

        return implode("\n", $postContent);
    }

    /**
     *  _addJSHandler method
     *
     * @param Cake\Event\Event $event of the current request
     * @return string   JS code for perosnal permissions
     */
    protected function _addJSHandler(Event $event)
    {
        $js = $event->subject()->Html->script(['RolesCapabilities.personal_permissions']);

        return $js;
    }

    /**
     *  _getListOfUsers method
     *
     * @return array    list of users t build dropdown
     */
    protected function _getListOfUsers()
    {
        $usersTable = TableRegistry::get('users');
        $users = $usersTable->find('list')
            ->where([
                'active' => 1
            ])
            ->limit(100)
            ->toArray();
        $users[''] = '';
        asort($users);

        return $users;
    }

    /**
     *  _getListOfActions method
     *
     * @return array    list of available actions
     */
    protected function _getListOfActions()
    {
        $actions[''] = '';
        foreach ($this->allowedActions as $action) {
            $actions[$action] = Inflector::humanize($action);
        }

        return $actions;
    }

    /**
     *  _getListOfGroups method
     *
     * @return array    list of available groups
     */
    protected function _getListOfGroups()
    {
        $groupsTable = TableRegistry::get('groups');
        $groups = $groupsTable->find('list')
            ->limit(100)
            ->toArray();
        $groups[''] = '';
        asort($groups);

        return $groups;
    }

    /**
     *  _getListOfPermissions method
     *
     * @param string $model         model name
     * @param string $foreignKey   uuid of record
     * @return array                list of already granted permissions
     */
    protected function _getListOfPermissions($model, $foreignKey)
    {
        $permissions = [];
        if (!empty($model) && !empty($foreignKey)) {
            $conditions = [];
            $conditions['model'] = $model;
            $conditions['foreign_key'] = $foreignKey;

            $permissionTable = TableRegistry::get('RolesCapabilities.PersonalPermissions');
            $query = $permissionTable->find('all', [
                'conditions' => $conditions,
                'limit' => 100,
            ]);
            $permissions = $query->all();
        }

        return $permissions;
    }

    /**
     *  _showExistingPermissions method
     *
     * @param array $permissions    list of existing personal permissions
     * @return string               table to display list of existing personal permissions
     */
    protected function _showExistingPermissions($permissions, Event $event)
    {
        $headers = ['ID', 'Model', 'Permission', 'Actions'];

        $table = [];
        $table[] = '<table class="table">';
        $table[] = '<tr>';

        foreach ($headers as $th) {
            $table[] = '<th>' . $th . '</th>';
        }
        $table[] = '</tr>';
        foreach ($permissions as $permission) {
            $entityTable = TableRegistry::get($permission->owner_model);
            $displayField = $entityTable->displayField();
            $table[] = '<tr>';
            $table[] = '<td>' . $entityTable->get($permission->owner_foreign_key)->$displayField . '</td>';
            $table[] = '<td>' . $permission->owner_model . '</td>';
            $table[] = '<td>' . $permission->type . '</td>';
            $table[] = '<td>';
            $table[] = $event->subject()->Form->postLink(
                '<i class="fa fa-trash"></i>',
                '/roles-capabilities/personal-permissions/delete/' . $permission->id,
                [
                    'class' => 'btn btn-default btn-sm',
                    'confirm' => 'Are you sure to delete this permission?',
                    'data' => [
                        'model' => $event->subject()->request->params['controller'],
                        'foreign_key' => $event->subject()->request->params['pass'][0],
                    ],
                    'escape' => false,
                ]
            );
            $table[] = '</td>';

            $table[] = '</tr>';
        }
        $table[] = '</table>';

        return implode("\n", $table);
    }
}
