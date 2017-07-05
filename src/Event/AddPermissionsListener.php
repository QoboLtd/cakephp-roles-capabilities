<?php

namespace RolesCapabilities\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use RolesCapabilities\CapabilityTrait;

class AddPermissionsListener implements EventListenerInterface
{
    use CapabilityTrait;

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
            'CsvMigrations.View.topMenu.beforeRender' => 'addPermissionsButton',
            'Integrations.View.topMenu.beforeRender' => 'addPermissionsButton',
            'Cms.Sites.toolbar.beforeRender' => 'addCmsPermissionsButton'
        ];
    }

    /**
     *  addPermissionsButton method.
     *
     * @param Cake\Event\Event $event of the current request
     * @param array $menu of the view page.
     * @param array $user currently logged in.
     * @return void
     */
    public function addCmsPermissionsButton(Event $event, array $menu, array $user)
    {
        $url = [
            'plugin' => $event->subject()->plugin,
            'controller' => $event->subject()->name,
            'action' => 'managePermissions',
        ];

        $table = TableRegistry::get('Cms.Sites');
        $query = $table->findByIdOrSlug(
            $event->subject->request->param('pass.0'),
            $event->subject->request->param('pass.0')
        );

        $site = $query->first();

        $params = $event->subject()->request->params;
        $params['pass'][0] = $site->id;

        if ($this->_checkAccess($url, $user)) {
            $content = $this->_addButton($event);
            $content .= $this->_addModalWindow($event, $menu, $params);

            $event->result .= $content;
        }
    }

    /**
     *  addPermissionsButton method
     *
     * @param Cake\Event\Event $event of the current request
     * @param array $menu of the view page.
     * @param array $user currently logged in.
     * @return void
     */
    public function addPermissionsButton(Event $event, array $menu, array $user)
    {
        $url = [
            'plugin' => $event->subject()->plugin,
            'controller' => $event->subject()->name,
            'action' => 'managePermissions',
        ];

        if ($this->_checkAccess($url, $user)) {
            $content = $this->_addButton($event);
            $content .= $this->_addModalWindow($event, $menu, $event->subject()->request->params);

            $event->result .= $content;
        }
    }

    /**
     *  addPermissionsModal method
     *
     * @param Cake\Event\Event $event of the current request
     * @param array $options of the view page.
     * @return void
     */
    public function addPermissionsModal(Event $event, $options)
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
            '#',
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
     * @param array $menu Menu
     * @return string   code of button
     */
    protected function _addDropdownButton(Event $event, array $menu)
    {
        $viewUrl = '/roles-capabilities/permissions/indexModal';
        $addUrl = '/roles-capabilities/permissions/addNew';
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
     * @param array $menu Menu
     * @param array $params Request parameters
     * @return string   code of modal window
     */
    protected function _addModalWindow(Event $event, array $menu, array $params)
    {
        $controllerName = $params['controller'];

        $users = $this->_getListOfUsers();

        $groups = $this->_getListOfGroups();

        $actions = $this->_getListOfActions();

        $permissions = $this->_getListOfPermissions(
            $params['controller'],
            $params['pass'][0]
        );

        $postContent = [];
        $postContent[] = '<div class="modal fade" id="permissions-modal-add" tabindex="-1" role="dialog" aria-labelledby="mySetsLabel">';
        $postContent[] = '<div class="modal-dialog" role="document">';
        $postContent[] = '<div class="modal-content">';
        $postContent[] = '<div class="modal-header">';
        $postContent[] = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $postContent[] = '<h4 class="modal-title" id="mySetsLabel">' . __('Add Permissions') . '</h4>';
        $postContent[] = '</div>'; // modal-header
        $postContent[] = '<div class="modal-body">';
        $postContent[] = $event->subject()->Form->create('RolesCapabilities.Permissions', ['url' => '/roles-capabilities/permissions/add', 'id' => 'modal-form-permissions-add']);
        $postContent[] = '<div class="sets-feedback-container"></div>';
        $postContent[] = $event->subject()->Form->hidden('foreign_key', ['value' => $params['pass'][0]]);
        $postContent[] = $event->subject()->Form->hidden('plugin', ['value' => $params['plugin']]);
        $postContent[] = $event->subject()->Form->hidden('model', ['value' => $params['controller']]);
        $postContent[] = '<div class="row"><div class="col-xs-6">';
        $postContent[] = $event->subject()->Form->input('type', [
            'type' => 'select',
            'options' => ['user' => 'User', 'group' => 'Group'],
            'class' => 'select2',
            'empty' => true,
            'id' => 'permission-type'
        ]);
        $postContent[] = '</div><div class="col-xs-6"><div id="type-inner-container">';
        $postContent[] = '</div></div></div>';

        $postContent[] = '<div class="row"><div class="col-xs-12 col-md-12">';
        $postContent[] = $event->subject()->Form->label(__('Permission'));
        $postContent[] = $event->subject()->Form->select('type', $actions, ['class' => 'select2', 'multiple' => false, 'required' => true]);
        $postContent[] = '</div></div>';

        $postContent[] = '<br /><div class="row"><div class="col-xs-12">';
        $postContent[] = $event->subject()->Form->button(__('Submit'), ['name' => 'btn_operation', 'value' => 'submit', 'class' => 'btn btn-primary pull-right']);
        $postContent[] = $event->subject()->Form->end();
        $postContent[] = '</div></div><br />';

        $postContent[] = '<div id="type-outer-container" class="hidden">';
        $postContent[] = '<div id="permission-user">';
        $postContent[] = $event->subject()->Form->label(__('User'));
        $postContent[] = $event->subject()->Form->select('user_id', $users, ['id' => 'permission-user', 'class' => 'select2', 'multiple' => false, 'required' => false]);
        $postContent[] = '</div>';
        $postContent[] = '<div id="permission-group">';
        $postContent[] = $event->subject()->Form->label(__('Groups'));
        $postContent[] = $event->subject()->Form->select('group_id', $groups, ['id' => 'permission-group', 'class' => 'select2', 'multiple' => false, 'required' => false]);
        $postContent[] = '</div>';
        $postContent[] = '</div>';

        $postContent[] = $this->_showExistingPermissions($permissions, $event);

        $postContent[] = '</div>'; //modal-body
        $postContent[] = '<div class="modal-footer">';
        $postContent[] = '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $postContent[] = '</div>';
        $postContent[] = '</div>'; // modal-content
        $postContent[] = '</div>'; //modal-dialog
        $postContent[] = '</div>'; // modal

        $event->subject()->Html->script(
            [
                'AdminLTE./plugins/select2/select2.full.min',
                'RolesCapabilities.select2.init',
                'RolesCapabilities.switch-target',
                'RolesCapabilities.permissions',
            ],
            ['block' => 'scriptBotton']
        );

        $event->subject()->Html->css(
            [
                'AdminLTE./plugins/select2/select2.min',
                'RolesCapabilities.select2-bootstrap.min',
            ],
            ['block' => 'css']
        );

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
        $js = $event->subject()->Html->script(['RolesCapabilities.permissions']);

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

            $permissionTable = TableRegistry::get('RolesCapabilities.Permissions');
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
     * @param array $permissions    list of existing permissions
     * @return string               table to display list of existing permissions
     */
    protected function _showExistingPermissions($permissions, Event $event)
    {
        $headers = ['ID', 'Model', 'Permission', 'Actions'];

        $table = [];
        $table[] = '<table class="table table-hover table-condensed table-vertical-align">';
        $table[] = '<thead>';
        $table[] = '<tr>';

        foreach ($headers as $th) {
            $table[] = '<th>' . $th . '</th>';
        }
        $table[] = '</tr>';
        $table[] = '</thead>';
        $table[] = '<tbody>';
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
                '/roles-capabilities/permissions/delete/' . $permission->id,
                [
                    'class' => 'btn btn-default btn-xs',
                    'confirm' => 'Are you sure to delete this permission?',
                    'data' => [
                        'plugin' => $event->subject()->request->params['plugin'],
                        'model' => $event->subject()->request->params['controller'],
                        'foreign_key' => $event->subject()->request->params['pass'][0],
                    ],
                    'escape' => false,
                ]
            );
            $table[] = '</td>';

            $table[] = '</tr>';
        }
        $table[] = '</tbody>';
        $table[] = '</table>';

        return implode("\n", $table);
    }
}
