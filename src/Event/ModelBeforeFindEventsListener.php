<?php
namespace RolesCapabilities\Event;

use ArrayObject;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class ModelBeforeFindEventsListener implements EventListenerInterface
{
    /**
     * List of tables that should be skipped during
     * record access check, to avoid infinite recursion.
     *
     * @var array
     */
    protected $_skipTables = ['roles', 'capabilities', 'users', 'groups', 'groups_roles', 'groups_users'];

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeFind' => 'checkRecordAccess',
        ];
    }

    /**
     * Check
     *
     * @param \Cake\Event\Event $event The beforeFind event that was fired.
     * @param \Cake\ORM\Query $query Query
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function checkRecordAccess(Event $event, Query $query, ArrayObject $options)
    {
        if (in_array($event->subject()->table(), $this->_skipTables)) {
            return;
        }

        $aclTable = TableRegistry::get('RolesCapabilities.Capabilities');

        $user = $aclTable->getCurrentUser();

        // skip any checks for superusers
        if (!empty($user['is_superuser']) && $user['is_superuser']) {
            return;
        }

        $userCapabilities = $aclTable->getUserCapabilities($user['id']);

        $tableName = get_class($event->subject());
        // Convert: MyPlugin\Model\Table\ArticlesTable
        // To: MyPlugin.Articles
        $tableName = App::shortName($tableName, 'Model/Table', 'Table');

        $controllerName = $aclTable->getControllerFullName(
            array_combine(['plugin', 'controller'], pluginSplit($tableName))
        );

        if (!$controllerName) {
            return;
        }

        $type = $aclTable->getTypeOwner();
        // @todo currently we are always assume index action, this probably needs to change in the future
        $actionCapabilities = $aclTable->getCapabilities($controllerName, ['index']);

        if (!isset($actionCapabilities[$type])) {
            return;
        }

        foreach ($actionCapabilities[$type] as $capability) {
            $query->where([$capability->getField() => $user['id']]);
        }
    }
}
