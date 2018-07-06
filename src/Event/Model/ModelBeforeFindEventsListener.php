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
namespace RolesCapabilities\Event\Model;

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
     * List target associations.
     *
     * @var array
     */
    protected $_targetAssociations = ['manyToMany', 'manyToOne'];

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

        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byInstance');
        if (in_array(get_class($table), $config)) {
            return;
        }

        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byRegistryAlias');
        if (in_array($table->getRegistryAlias(), $config)) {
            return;
        }

        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byTableName');
        if (in_array($table->getTable(), $config)) {
            return;
        }

        // get current user
        $user = TableRegistry::get('RolesCapabilities.Capabilities')->getCurrentUser();

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

        $setNullStatement = true;
        $result = $this->filterQuery($query, $table, $user, $controllerName);

        if ($result) {
            $setNullStatement = false;
        }
        // Check supervisor access
        if (!empty($user['is_supervisor']) && $user['is_supervisor']) {
            $users = Utils::getReportToUsers($user['id']);

            foreach ($users as $rec) {
                $result = $this->filterQuery($query, $table, $rec->toArray(), $controllerName, true);
            }

            if ($result) {
                $setNullStatement = false;
            }
        }

        if ($setNullStatement) {
            // if user has neither owner nor full capability on current action then filter out all records
            $primaryKey = $table->primaryKey();
            $query->where([$table->aliasField($primaryKey) => null]);
        }
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
     * @param bool $useOr specified how to connect where statements - via AND (default) or OR
     * @return bool
     */
    protected function filterQuery(Query $query, Table $table, array $user, $controllerName, $useOr = false)
    {
        $capAccess = new CapabilitiesAccess();
        // get current user capabilities
        $userCaps = $capAccess->getUserCapabilities($user['id']);

        // @todo currently we are always assume index action, this probably needs to change in the future
        $actionCaps = Utils::getCapabilities($controllerName, ['index']);

        if ($this->hasFullAccess($actionCaps, $userCaps)) {
            return true;
        }

        $where = [];
        $ownerFields = $this->getOwnerFields($table, $actionCaps, $user, $userCaps);
        if (!empty($ownerFields)) {
            $where = array_merge($where, $ownerFields);
        }

        $permissions = $this->getPermissions($table, $user);
        if (!empty($permissions)) {
            $where = array_merge($where, $permissions);
        }

        $joins = $this->getParentJoins($table, $actionCaps, $user, $userCaps);

        if (!empty($joins)) {
            foreach ($joins as $name => $conditions) {
                $query->leftJoinWith($name, function ($q) {
                    return $q->applyOptions(['accessCheck' => false]);
                });

                $where = array_merge($where, $conditions);
            }
        }

        if (!empty($where)) {
            $method = 'where';
            if ($useOr) {
                // FIXME: orWhere is deprecated in 3.6 - https://api.cakephp.org/3.6/class-Cake.Database.Query.html#_orWhere
                $method = 'orWhere';
            }

            $query->$method(['OR' => $where]);
        }

        if (!empty($where) || !empty($joins)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has full access.
     *
     * @param array $actionCaps Action capabilities
     * @param array $userCaps User capabilities
     * @return bool
     */
    protected function hasFullAccess(array $actionCaps, array $userCaps)
    {
        $type = Utils::getTypeFull();

        if (!isset($actionCaps[$type])) {
            return false;
        }

        // check user capabilities against action's full capabilities
        foreach ($actionCaps[$type] as $actionCap) {
            // if current action's full capability is matched in user's capabilities just return
            if (in_array($actionCap->getName(), $userCaps)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return owner fields and value.
     *
     * @param object $table to apply capabilities
     * @param array $actionCaps Action capabilities
     * @param array $user User info
     * @param array $userCaps User capabilities
     * @return array
     */
    protected function getOwnerFields(Table $table, array $actionCaps, array $user, array $userCaps)
    {
        $result = [];

        $type = Utils::getTypeOwner();
        if (!isset($actionCaps[$type])) {
            return $result;
        }

        // check user capabilities against action's owner capabilities
        foreach ($actionCaps[$type] as $capability) {
            if (!in_array($capability->getName(), $userCaps)) {
                continue;
            }
            // if user has owner capability for current action add appropriate conditions to where clause
            $result[$table->aliasField($capability->getField())] = $user['id'];
        }

        return $result;
    }

    /**
     * Return permissions.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param array $user User info
     * @return array
     */
    protected function getPermissions(Table $table, array $user)
    {
        $result = [];

        $groups = TableRegistry::get('RolesCapabilities.Capabilities')->getUserGroups($user['id']);

        $query = TableRegistry::get('RolesCapabilities.Permissions')
            ->find('all')
            ->select('foreign_key')
            ->where([
                // @todo this might causes issues in the future with
                // APP table's name matching a plugin's table name.
                'model' => $table->alias(),
                'type IN ' => ['view'],
                'OR' => [
                    ['owner_foreign_key IN ' => array_keys($groups), 'owner_model' => 'Groups'],
                    ['owner_foreign_key' => $user['id'], 'owner_model' => 'Users']
                ]
            ])
            ->applyOptions(['accessCheck' => false]);

        if ($query->isEmpty()) {
            return $result;
        }

        $primaryKey = $table->aliasField($table->getPrimaryKey());

        $values = [];
        foreach ($query->all() as $permission) {
            $values[] = $permission->foreign_key;
        }

        $result[$primaryKey . ' IN'] = array_unique($values);

        return $result;
    }

    /**
     * Return parent association joins.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param array $actionCaps Action capabilities
     * @param array $user User info
     * @param array $userCaps User capabilities
     * @return array
     */
    protected function getParentJoins(Table $table, array $actionCaps, array $user, array $userCaps)
    {
        $result = [];

        $type = Utils::getTypeParent();
        if (!isset($actionCaps[$type])) {
            return $result;
        }

        $modules = [];
        // check parent capabilities against action's parent capabilities
        foreach ($actionCaps[$type] as $capability) {
            if (!in_array($capability->getName(), $userCaps)) {
                continue;
            }
            // if user has owner capability for current action add appropriate conditions to where clause
            $modules = $capability->getParentModules();
        }

        if (empty($modules)) {
            return $result;
        }

        $primaryKey = $table->aliasField($table->getPrimaryKey());
        foreach ($table->associations() as $association) {
            if (!in_array($association->type(), $this->_targetAssociations)) {
                continue;
            }

            $targetTable = $association->getTarget();
            $targetName = App::shortName(get_class($targetTable), 'Model/Table', 'Table');
            if (!in_array($targetName, $modules)) {
                continue;
            }

            $fields = Utils::getTableAssignationFields($targetTable);
            if (empty($fields)) {
                continue;
            }

            $conditions = [];
            foreach ($fields as $field) {
                $conditions[$targetTable->aliasField($field)] = $user['id'];
            }

            $foreignKey = $targetTable->aliasField($association->getForeignKey());
            $result[$association->getName()] = $conditions;
        }

        return $result;
    }
}
