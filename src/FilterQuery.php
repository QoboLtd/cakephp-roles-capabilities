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
namespace RolesCapabilities;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use RolesCapabilities\Access\Utils;

/**
 * This class is responsible for filtering all application queries
 * by applying current user capabilities and permissions.
 */
final class FilterQuery
{
    /**
     * Query instance.
     *
     * @var \Cake\Datasource\QueryInterface
     */
    private $query;

    /**
     * Current user and action capabilities.
     *
     * @var array
     */
    private $capabilities = [];

    /**
     * Query target table.
     *
     * @var \Cake\Datasource\RepositoryInterface
     */
    private $table;

    /**
     * Current user info.
     *
     * @var array
     */
    private $user;

    /**
     * Association types supported for parent join functionality.
     *
     * @var array
     */
    private $parentJoinAssocations = ['manyToMany', 'manyToOne'];

    /**
     * Filterable flag
     *
     * True by default and only set to false if specific conditions apply.
     *
     * @var bool
     */
    private $filterable = true;

    /**
     * Constructor method.
     *
     * @param \Cake\Datasource\QueryInterface $query Query object
     * @param \Cake\Datasource\RepositoryInterface $table Table instance
     * @param array $user User info
     * @return void
     */
    public function __construct(QueryInterface $query, RepositoryInterface $table, array $user)
    {
        $this->query = $query;
        $this->table = $table;
        $this->user = $user;

        if (! $this->isFilterable()) {
            $this->filterable = false;

            return;
        }

        $this->capabilities = [
            // get current user capabilities
            'user' => Utils::fetchUserCapabilities($this->user['id']),
            // @todo currently we are always assume index action, this probably needs to change in the future
            'action' => Utils::getCapabilities($this->getControllerClassName(), ['index'])
        ];
    }

    /**
     * Controller class name getter.
     *
     * @return false|string False if the class is not found or namespaced class name
     */
    private function getControllerClassName()
    {
        return App::className(
            App::shortName(get_class($this->table), 'Model/Table', 'Table') . 'Controller',
            'Controller'
        );
    }

    /**
     * Validates if Query is filterable.
     *
     * @return bool
     */
    private function isFilterable()
    {
        if (empty($this->user)) {
            return false;
        }

        if (! array_key_exists('id', $this->user)) {
            return false;
        }

        if ($this->isSuperuser()) {
            return false;
        }

        if ($this->isSkipTable()) {
            return false;
        }

        $controllerName = $this->getControllerClassName();

        // no relevant controller found for specified table
        if (! $controllerName) {
            return false;
        }

        // table's controller is cake's default controller, this is probably a many-to-many join table
        if ('Cake\Controller\Controller' === $controllerName) {
            return false;
        }

        return true;
    }

    /**
     * Validates if provided user is a superuser.
     *
     * @return bool
     */
    private function isSuperuser()
    {
        if (! isset($this->user['is_superuser'])) {
            return false;
        }

        return (bool)$this->user['is_superuser'];
    }

    /**
     * Validates if provided user is supervisor.
     *
     * @return bool
     */
    private function isSupervisor()
    {
        if (! isset($this->user['is_supervisor'])) {
            return false;
        }

        return (bool)$this->user['is_supervisor'];
    }

    /**
     *  Validates if provided table must be skipped from access checks.
     *
     * @return bool
     */
    private function isSkipTable()
    {
        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byInstance');
        if (in_array(get_class($this->table), $config)) {
            return true;
        }

        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byRegistryAlias');
        if (in_array($this->table->getRegistryAlias(), $config)) {
            return true;
        }

        $config = (array)Configure::read('RolesCapabilities.ownerCheck.skipTables.byTableName');
        if (in_array($this->table->getTable(), $config)) {
            return true;
        }

        return false;
    }

    /**
     * Executes Query filtering functionality.
     *
     * Filter Query results by applying where clause conditions based on current user capabilities. If current user has
     * limited access, then the appropriate condition will be applied to the SQL where clause.
     *
     * Limited access includes:
     * - Index or view records only assigned to him.
     * - Index or view records assigned to users who report to him (supervisor access).
     * - Index or view records related to a parent module which the user has one of the two conditions above applied.
     *
     * @return \Cake\Datasource\QueryInterface
     */
    public function execute()
    {
        // query is not filterable, return it as is.
        if (! $this->filterable) {
            return $this->query;
        }

        if ($this->hasFullAccess()) {
            return $this->query;
        }

        $where = $this->getWhereClause($this->user);
        if (! empty($where)) {
            // apply all conditions using the OR operator
            $where = ['OR' => $where];
        }

        if (empty($where)) {
            // if user has neither owner nor full capability on current action then filter out all records
            $where = [$this->table->aliasField($this->table->primaryKey()) => null];
        }

        $this->query->where($where);

        return $this->query;
    }

    /**
     * Generates conditions for where clause.
     *
     * @return array
     */
    private function getWhereClause()
    {
        $result = array_merge($this->getOwnerFields(), $this->getPermissions());
        $result = array_merge($result, $this->getParentJoinsWhereClause());
        $result = array_merge_recursive($result, $this->getSupervisorWhereClause());

        return $result;
    }

    /**
     * Generates parent joins conditions for where clause.
     *
     * @return array
     */
    private function getParentJoinsWhereClause()
    {
        $joins = $this->getParentJoins();
        if (empty($joins)) {
            return [];
        }

        $result = [];
        foreach ($joins as $name => $conditions) {
            $result = array_merge($result, $conditions);

            $this->query->leftJoinWith($name, function ($q) {
                return $q->applyOptions(['accessCheck' => false]); // disable access check on joins
            });
        }

        return $result;
    }

    /**
     * Generates supervisor conditions for where clause.
     *
     * It recursively calls getWhereClause() method by re-instantiating \RolesCapabilities\FilterQuery
     * and setting each subordinate as the instance $user property.
     *
     * @return array
     */
    private function getSupervisorWhereClause()
    {
        if (! $this->isSupervisor()) {
            return [];
        }

        $result = [];
        foreach (Utils::getReportToUsers($this->user['id']) as $subordinate) {
            $result = array_merge_recursive(
                $result,
                (new FilterQuery($this->query, $this->table, $subordinate->toArray()))->getWhereClause()
            );
        }

        return $result;
    }

    /**
     * Check if user has full access.
     *
     * @return bool
     */
    private function hasFullAccess()
    {
        if (! isset($this->capabilities['action'][Utils::getTypeFull()])) {
            return false;
        }

        // check user capabilities against action's full capabilities
        foreach ($this->capabilities['action'][Utils::getTypeFull()] as $capability) {
            // if current action's full capability is matched in user's capabilities just return
            if (in_array($capability->getName(), $this->capabilities['user'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return owner fields and value.
     *
     * @return array
     */
    private function getOwnerFields()
    {
        if (! isset($this->capabilities['action'][Utils::getTypeOwner()])) {
            return [];
        }

        $result = [];
        // check user capabilities against action's owner capabilities
        foreach ($this->capabilities['action'][Utils::getTypeOwner()] as $capability) {
            if (in_array($capability->getName(), $this->capabilities['user'])) {
                // if user has owner capability for current action add appropriate conditions to where clause
                $result[$this->table->aliasField($capability->getField()) . ' IN'] = $this->user['id'];
            }
        }

        return $result;
    }

    /**
     * Return permissions.
     *
     * @return array
     */
    private function getPermissions()
    {
        $groups = TableRegistry::getTableLocator()->get('RolesCapabilities.Capabilities')
            ->getUserGroups($this->user['id']);

        $query = TableRegistry::getTableLocator()->get('RolesCapabilities.Permissions')
            ->find('all')
            ->select('foreign_key')
            ->where([
                // WARNING: this might conflict with APP table's name matching a plugin's table name
                'model' => $this->table->getAlias(),
                'type IN ' => ['view'],
                'OR' => [
                    ['owner_foreign_key IN ' => array_keys($groups), 'owner_model' => 'Groups'],
                    ['owner_foreign_key' => $this->user['id'], 'owner_model' => 'Users']
                ]
            ])
            ->applyOptions(['accessCheck' => false]);

        if ($query->isEmpty()) {
            return [];
        }

        $values = [];
        foreach ($query->all() as $permission) {
            $values[] = $permission->foreign_key;
        }

        return [$this->table->aliasField($this->table->getPrimaryKey()) . ' IN' => array_unique($values)];
    }

    /**
     * Return parent association joins.
     *
     * @return array
     */
    private function getParentJoins()
    {
        $modules = $this->getParentModules();

        if (empty($modules)) {
            return [];
        }

        $result = [];
        foreach ($this->table->associations() as $association) {
            $conditions = $this->getParentJoin($association, $modules);
            if (empty($conditions)) {
                continue;
            }

            $result[$association->getName()] = $conditions;
        }

        return $result;
    }

    /**
     * Parent modules getter.
     *
     * Returns the list of parent modules based on current action parent capabilities,
     * filtered by current user capabilities.
     *
     * @return array
     */
    private function getParentModules()
    {
        if (! isset($this->capabilities['action'][Utils::getTypeParent()])) {
            return [];
        }

        $result = [];
        foreach ($this->capabilities['action'][Utils::getTypeParent()] as $capability) {
            if (in_array($capability->getName(), $this->capabilities['user'])) {
                $result = $capability->getParentModules();
            }
        }

        return $result;
    }

    /**
     * Return parent association join.
     *
     * @param \Cake\ORM\Association $association Association instance
     * @param array $modules Parent modules
     * @return array
     */
    private function getParentJoin(Association $association, array $modules)
    {
        if (! in_array($association->type(), $this->parentJoinAssocations)) {
            return [];
        }

        $targetTable = $association->getTarget();
        $targetName = App::shortName(get_class($targetTable), 'Model/Table', 'Table');
        if (! in_array($targetName, $modules)) {
            return [];
        }

        $result = [];
        foreach (Utils::getTableAssignationFields($targetTable) as $field) {
            $result[$targetTable->aliasField($field) . ' IN'] = $this->user['id'];
        }

        return $result;
    }
}
