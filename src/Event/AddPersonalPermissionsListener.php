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
     */
    public function addPersonalPermissionsButton(Event $event, array $menu, array $user)
    {
        $content = $this->_addButton();
        $content .= $this->_addModalWindow($event);

        $event->result = $content;
    }

    /**
     *  addPersonalPermissionsModal method
     *
     * @param Cake\Event\Event $event of the current request
     * @param array $menu of the view page.
     * @param array $user currently logged in.
     */
    public function addPersonalPermissionsModal(Event $event, array $options)
    {
        $content = $this->_addModalWindow($event);
        $content .= $this->_addJSHandler($event);

        $event->result = $content;
    }

    /**
     *  _addButton method
     *
     * @return string   code of button
     */
    protected function _addButton()
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

        $postContent[] = '<div class="modal fade" id="permissions-modal-add" tabindex="-1" role="dialog" aria-labelledby="mySetsLabel">';
        $postContent[] = $event->subject()->Form->create('Sets', ['url' => false, 'id' => 'modal-form-permissions-add']);
        $postContent[] = '<div class="modal-dialog" role="document">';
        $postContent[] = '<div class="modal-content">';
        $postContent[] = '<div class="modal-header">';
        $postContent[] = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $postContent[] = '<h4 class="modal-title" id="mySetsLabel">' . __('Add Permissions') . '</h4>';
        $postContent[] = '</div>'; // modal-header
        $postContent[] = '<div class="modal-body">';
        $postContent[] = '<div class="sets-feedback-container"></div>';
        $postContent[] = $event->subject()->Form->hidden('Sets.module', ['value' => Inflector::underscore($controllerName)]);

        $postContent[] = $event->subject()->Form->select('user', $users, ['class' => 'select2', 'multiple' => false]);

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
     * @return string   JS code for perosnal permissions
     */
    protected function _addJSHandler()
    {        
        return $event->subject()->Html->script(['RolesCapabilities.personal_permissions'], ['block' => 'scriptBottom']);
    }
}
