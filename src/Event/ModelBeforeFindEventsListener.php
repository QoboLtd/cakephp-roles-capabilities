<?php
namespace RolesCapabilities\Event;

use ArrayObject;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Access\CapabilitiesAccess;
use RolesCapabilities\Access\Utils;

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
            'Model.beforeFind' => 'filterByUserCapabilities',
        ];
    }

    /**
     * Query filtering method based on current user capabilities.
     *
     * Filtering can be skipped on per query basis by passing the
     * 'accessCheck' option and set it to false.
     *
     * Additionally filtering can be skipped on per table basis by
     * defining the table name in the plugin's configuration under
     * variable 'RolesCapabilities.ownerCheck.skipTables'.
     *
     * @param \Cake\Event\Event $event The beforeFind event that was fired.
     * @param \Cake\ORM\Query $query Query
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function filterByUserCapabilities(Event $event, Query $query, ArrayObject $options)
    {
        if (isset($options['accessCheck']) && !$options['accessCheck']) {
            return;
        }

        // current table
        $table = $event->subject();

        $skipTables = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables');
        // skip if current table is in the list of skipped tables
        if (in_array($table->table(), $skipTables)) {
            return;
        }

        // get acl table
        $aclTable = TableRegistry::get('RolesCapabilities.Capabilities');

        // get current user
        $user = $aclTable->getCurrentUser();
        // skip if user not set or if is a superuser
        if (empty($user) || (!empty($user['is_superuser']) && $user['is_superuser'])) {
            return;
        }

        // convert: 'MyPlugin\Model\Table\ArticlesTable' to: 'MyPlugin.Articles'
        $tableName = App::shortName(get_class($table), 'Model/Table', 'Table');

        // convert: 'MyPlugin.Articles' to: ['plugin' => 'MyPlugin', 'controller' => 'Articles']
        $url = array_combine(['plugin', 'controller'], pluginSplit($tableName));

        $controllerName = Utils::getControllerFullName($url);
        // skip if controller class name was not found
        if (!$controllerName) {
            return;
        }

        $this->_filterQuery($query, $table, $user, $controllerName);
    }

    /**
     *
     * Filter query results by applying where clause conditions
     * based on current user capabilities.
     *
     * If current user has limited access to index or view records
     * only assigned to him, then the appropriate condition will
     * be applied to the sql where clause.
     *
     * @param \Cake\ORM\Query $query Query
     * @param \Cake\ORM\Table $table Table instance
     * @param array $user User info
     * @param string $controllerName Namespaced controller name
     * @return void
     */
    protected function _filterQuery(Query $query, Table $table, array $user, $controllerName)
    {
        $capAccess = new CapabilitiesAccess();
        // get current user capabilities
        $userCapabilities = $capAccess->getUserCapabilities($user['id']);

        // @todo currently we are always assume index action, this probably needs to change in the future
        $actionCapabilities = Utils::getCapabilities($controllerName, ['index']);

        $fullType = Utils::getTypeFull();
        // check user capabilities against action's full capabilities
        if (isset($actionCapabilities[$fullType])) {
            foreach ($actionCapabilities[$fullType] as $capability) {
                // if current action's full capability is matched in user's capabilities just return
                if (in_array($capability->getName(), $userCapabilities)) {
                    return;
                }
            }
        }

        // check if user has owner capability for current action,
        // if it does modify the query accordingly and return.
        $ownerType = Utils::getTypeOwner();
        // check user capabilities against action's owner capabilities
        if (isset($actionCapabilities[$ownerType])) {
            $hasOwnerType = false;
            foreach ($actionCapabilities[$ownerType] as $capability) {
                if (!in_array($capability->getName(), $userCapabilities)) {
                    continue;
                }
                // if user has owner capability for current action add appropriate conditions to where clause
                $query->where([$capability->getField() => $user['id']]);
                $hasOwnerType = true;
            }

            if ($hasOwnerType) {
                return;
            }
        }

        $allowedEntities = $this->_getAllowedEntities($table, $user);
        if (!empty($allowedEntities)) {
            $query->where([$table->alias() . '.id IN ' => array_keys($allowedEntities)]);

            return;
        }

        // if user has neither owner nor full capability on current action then filter out all records
        $primaryKey = $table->primaryKey();
        $query->where([$table->aliasField($primaryKey) => null]);
    }

    /**
     * _getAllowedEntities method
     *
     * @param \Cake\ORM\Table $table    Table instance
     * @param array $user               user's details
     */
    protected function _getAllowedEntities(Table $table, array $user)
    {
        $groups = TableRegistry::get('RolesCapabilities.Capabilities')->getUserGroups($user['id']);

        $permissions = TableRegistry::get('RolesCapabilities.Permissions')
            ->find('all')
            ->select('foreign_key')
            ->where([
                'model' => $table->alias(),
                'type IN ' => ['view'],
                'OR' => [
                            [
                                'owner_foreign_key IN ' => array_keys($groups),
                                'owner_model' => 'Groups',
                            ],
                            [
                                'owner_foreign_key' => $user['id'],
                                'owner_model' => 'Users',
                            ]
                    ]
            ])
            ->applyOptions(['accessCheck' => false])
            ->toArray();
        $result = [];
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                $result[$permission->foreign_key] = 1;
            }
        }

        return $result;
    }
}
