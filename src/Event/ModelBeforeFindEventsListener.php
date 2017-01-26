<?php
namespace RolesCapabilities\Event;

use ArrayObject;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class ModelBeforeFindEventsListener implements EventListenerInterface
{
    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeFind' => 'filterByUserPermissions',
        ];
    }

    /**
     * Filter query results by applying where clause conditions
     * based on current user capabilities.
     *
     * If current user has limited access to index or view records
     * only assigned to him, then the appropriate condition will
     * be applied to the sql where clause.
     *
     *
     *
     * @param \Cake\Event\Event $event The beforeFind event that was fired.
     * @param \Cake\ORM\Query $query Query
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function filterByUserPermissions(Event $event, Query $query, ArrayObject $options)
    {
        $skipTables = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables');
        if (in_array($event->subject()->table(), $skipTables)) {
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
