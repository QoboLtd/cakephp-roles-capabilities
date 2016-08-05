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
        $table = TableRegistry::get('RolesCapabilities.Capabilities');

        // current request parameters
        $request = $table->getCurrentRequest();

        // skip if current model does not match request's model
        if (array_diff(
            pluginSplit($event->subject()->registryAlias()),
            [$request['plugin'], $request['controller']]
        )) {
            return;
        }

        // get capability owner type identifier
        $type = $table->getTypeOwner();

        // get user's action capabilities
        $userActionCapabilities = $table->getUserActionCapabilities();

        // skip if no user's action capabilities found or no user's action
        // owner specific capabilities found for current request's action
        if (empty($userActionCapabilities)) {
            return;
        }

        if (!isset($userActionCapabilities[$request['plugin']][$request['controller']][$request['action']][$type])) {
            return;
        }

        // set query where clause based on user's owner capabilities assignment fields
        foreach ($userActionCapabilities[$request['plugin']][$request['controller']][$request['action']][$type] as $userActionCapability) {
            $query->where([$userActionCapability->getField() => $table->getCurrentUser('id')]);
        }
    }
}
