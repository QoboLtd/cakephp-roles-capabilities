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
            'CsvMigrations.View.Body.Bottom' => 'addPersonalPermissionsModal',
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
        $content = $this->_addButton($event);
        $content .= $this->_addModalWindow($event);

        $event->result = $content;
    }

    /**
     *  addPersonalPermissionsModal method
     *
     * @param Cake\Event\Event $event of the current request
     * @param array $options of the view page.
     * @return void
     */
    public function addPersonalPermissionsModal(Event $event, array $options)
    {
        $content = $this->_addModalWindow($event);

        debug($options);
        die();
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
        return  $event->subject()->Html->link(
            __('Permissions'),
            '#',
            [
                'class' => 'btn btn-default', 
                'data-toggle' => "modal",
                'data-target' => "#permissions-modal-add"
            ]
        );
    }

    /**
     *  _addDropdownButton method
     *
     * @param Cake\Event\Event $event of the current request
     * @return string   code of button
     */
    protected function _addDropdownButton(Event $event)
    {
        $button = [];

        $button[] = '<div class="btn-group btn-group-sm">';
        $button[] = '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $button[] = __('Permissions') . '<span class="caret"></span>';
        $button[] = '<span class="sr-only">Toggle Dropdown</span>';
        $button[] = '</button>';
        $button[] = '<ul class="dropdown-menu">';

        foreach ($this->allowedActions as $action) {
            $button[] = '<li><a href="#" id="' . $action . '" data-toggle="modal" data-target="#permissions-modal-add">' . Inflector::humanize($action) . '</a></li>';
        }
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
    protected function _addModalWindow(Event $event)
    {
        $controllerName = $event->subject()->request->params['controller'];
        $postContent = [];

        $usersTable = TableRegistry::get('users');
        $users = $usersTable->find('list')
            ->where([
                'active' => 1
            ])
            ->limit(100)
            ->toArray();
        $users[''] = '';
        asort($users);

        $actions[''] = '';
        foreach ($this->allowedActions as $action) {
            $actions[$action] = Inflector::humanize($action);
        }

        $postContent[] = '<div class="modal fade" id="permissions-modal-add" tabindex="-1" role="dialog" aria-labelledby="mySetsLabel">';
        $postContent[] = $event->subject()->Form->create('RolesCapabilities.PersonalPermissions', ['url' => '/roles-capabilities/personal-permissions/add', 'id' => 'modal-form-permissions-add']);
        $postContent[] = '<div class="modal-dialog" role="document">';
        $postContent[] = '<div class="modal-content">';
        $postContent[] = '<div class="modal-header">';
        $postContent[] = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $postContent[] = '<h4 class="modal-title" id="mySetsLabel">' . __('Add Permissions') . '</h4>';
        $postContent[] = '</div>'; // modal-header
        $postContent[] = '<div class="modal-body">';
        $postContent[] = '<div class="sets-feedback-container"></div>';
        $postContent[] = $event->subject()->Form->hidden('foreign_key', ['value' => $event->subject()->request->params['pass'][0]]);
        $postContent[] = $event->subject()->Form->hidden('model', ['value' => $event->subject()->request->params['controller']]);
        $postContent[] = '<div class="row"><div class="col-xs-12 col-md-12">';
        $postContent[] = $event->subject()->Form->label(__('User'));
        $postContent[] = $event->subject()->Form->select('user_id', $users, ['class' => 'select2', 'multiple' => false, 'required' => true]);
        $postContent[] = '</div></div>';

        $postContent[] = '<div class="row"><div class="col-xs-12 col-md-12">';
        $postContent[] = $event->subject()->Form->label(__('Permission'));
        $postContent[] = $event->subject()->Form->select('type', $actions, ['class' => 'select2', 'multiple' => false, 'required' => true]);
        $postContent[] = '</div></div>';
        $postContent[] = '<div class="row"><div class="col-xs-12 col-md-12">';
        $postContent[] = $event->subject()->Form->input('is_active', ['type' => 'checkbox']);
        $postContent[] = '</div></div>';

        $postContent[] = '</div>'; //modal-body
        $postContent[] = '<div class="modal-footer">';
        $postContent[] = $event->subject()->Form->button(__('Submit'), ['name' => 'btn_operation', 'value' => 'submit', 'class' => 'btn btn-primary']);
        $postContent[] = '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $postContent[] = '</div>';
        $postContent[] = '</div>'; // modal-content
        $postContent[] = '</div>'; //modal-dialog
        $postContent[] = $event->subject()->Form->end();
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
        return $event->subject()->Html->script(['RolesCapabilities.personal_permissions'], ['block' => 'scriptBottom']);
    }
}
