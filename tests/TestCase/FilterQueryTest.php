<?php
namespace RolesCapabilities\Test\TestCase;

use Cake\Core\Configure;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase as TestCase;
use RolesCapabilities\FilterQuery;

class FilterQueryTest extends TestCase
{
    /**
     * @var object Users table
     */
    private $Users;

    /**
     * @var array Holds the several users
     */
    private $user_array = [];

    /**
     * @var \Cake\ORM\Table Table instance
     */
    private $table;

    /**
     * @var array Fixtures list
     */
    public $fixtures = [
        'plugin.roles_capabilities.users',
        'plugin.roles_capabilities.groups_roles',
        'plugin.roles_capabilities.groups_users'
    ];

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Return method back
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Extend setUp method of parent
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Users') ? [] : ['className' => '\Cake\ORM\Table'];
        $this->Users = TableRegistry::get('Users', $config);

        $this->user_array = [
            'is_superuser_no_supervisor' => $this->Users->get('00000000-0000-0000-0000-000000000001')->toArray(),
            'no_superuser_is_supervisor' => $this->Users->get('00000000-0000-0000-0000-000000000002')->toArray()
        ];
    }

    /**
     * Test isFilterable with no user id
     */
    public function testIsFilterableEmptyUser(): void
    {
        $filter = new FilterQuery($this->Users->find(), $this->Users, []);
        $is_filterable = $this->invokeMethod($filter, 'isFilterable');
        $this->assertEquals(false, $is_filterable);
    }

    /**
     * Test isFilterable with the user id missing from the user array
     */
    public function testIsFilterableUserIdMissing(): void
    {
        $filter = new FilterQuery($this->Users->find(), $this->Users, ['name' => 'admin']);
        $is_filterable = $this->invokeMethod($filter, 'isFilterable');
        $this->assertEquals(false, $is_filterable);
    }

    /**
     * Test isFilterable with user id
     */
    public function testIsFilterableUserIdGiven(): void
    {
        $filter = new FilterQuery($this->Users->find(), $this->Users, $this->user_array['is_superuser_no_supervisor']);
        $is_filterable = $this->invokeMethod($filter, 'isFilterable');
        $this->assertEquals(false, $is_filterable);
    }

    /**
     * Test isFilterable with user id
     */
    public function testIsFilterableIsSkipTable(): void
    {
        Configure::write('RolesCapabilities.ownerCheck.skipTables.byTableName', ['users']);
        $filter = new FilterQuery($this->Users->find(), $this->Users, $this->user_array['no_superuser_is_supervisor']);
        $is_filterable = $this->invokeMethod($filter, 'isFilterable');
        $this->assertEquals(false, $is_filterable);
    }

    /**
     * Test isFilterable with user id and the user is not a supervisor
     */
    public function testIsFilterableNoSuperUser(): void
    {
        $filter = new FilterQuery($this->Users->find(), $this->Users, $this->user_array['no_superuser_is_supervisor']);
        $is_filterable = $this->invokeMethod($filter, 'isFilterable');
        $this->assertEquals(false, $is_filterable);
    }

    /**
     * Test isSuperuser with missing the superuser attribute
     */
    public function testIsSuperuserWithMissingAttribute(): void
    {
        $user = $this->user_array['is_superuser_no_supervisor'];
        unset($user['is_superuser']);
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $is_superuser = $this->invokeMethod($filter, 'isSuperuser');
        $this->assertEquals(false, $is_superuser);
    }

    /**
     * Test isSkipTable by instance
     */
    public function testIsSkipTableByInstance(): void
    {
        $config = TableRegistry::exists('Roles') ? [] : ['className' => 'RolesCapabilities\Model\Table\RolesTable'];
        $table = TableRegistry::get('Roles', $config);

        $filter = new FilterQuery($table->find(), $table, []);
        $is_skipTable = $this->invokeMethod($filter, 'isSkipTable');
        $this->assertEquals(true, $is_skipTable);
    }

    /**
     * Test isSkipTable by registry alias
     */
    public function testIsSkipTableByRegistryAlias(): void
    {
        $config = TableRegistry::exists('GroupsUsers') ? [] : ['className' => '\Cake\ORM\Table'];
        $table = TableRegistry::get('GroupsUsers', $config);

        $filter = new FilterQuery($table->find(), $table, []);
        $is_skipTable = $this->invokeMethod($filter, 'isSkipTable');
        $this->assertEquals(true, $is_skipTable);
    }

    /**
     * Test isSkipTable by table name
     */
    public function testIsSkipTableByTableName(): void
    {
        Configure::write('RolesCapabilities.ownerCheck.skipTables.byTableName', ['users']);
        $filter = new FilterQuery($this->Users->find(), $this->Users, []);
        $is_skipTable = $this->invokeMethod($filter, 'isSkipTable');
        $this->assertEquals(true, $is_skipTable);
    }

    /**
     * Test isSupervisor
     */
    public function testIsSupervisor(): void
    {
        $user = $this->user_array['no_superuser_is_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $is_supervisor = $this->invokeMethod($filter, 'isSupervisor');
        $this->assertEquals(true, $is_supervisor);
    }

    /**
     * Test isSupervisor with missing the supervisor attribute
     */
    public function testIsSupervisorWithMissingAttribute(): void
    {
        $user = $this->user_array['no_superuser_is_supervisor'];
        unset($user['is_supervisor']);
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $is_supervisor = $this->invokeMethod($filter, 'isSupervisor');
        $this->assertEquals(false, $is_supervisor);
    }

    /**
     * Test getParentJoints method when module is empty
     */
    public function testGetParentJoinsEmpty()
    {
        $user = $this->user_array['no_superuser_is_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $get_parent_joints = $this->invokeMethod($filter, 'getParentJoins');
        $this->assertEquals([], $get_parent_joints);
    }

    /**
     * Test getParentJoin method
     */
    public function testGetParentJoin()
    {
        $config = TableRegistry::exists('Capabilities') ? [] : ['className' => 'RolesCapabilities\Model\Table\CapabilitiesTable'];
        $table = TableRegistry::get('Capabilities', $config);

        $user = $this->user_array['no_superuser_is_supervisor'];

        $filter = new FilterQuery($table->find(), $table, $user);
        foreach ($table->associations() as $association) {
            $get_parent_modules = $this->invokeMethod($filter, 'getParentJoin', [$association, ["roles"]]);
            $this->assertEquals([], $get_parent_modules);
        }
    }

    /**
     * Test public method execute
     */
    public function testExecute()
    {
        $user = $this->user_array['no_superuser_is_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $this->assertInstanceOf('\Cake\Datasource\QueryInterface', $filter->execute(), 'message');
    }

    /**
     * Test getOwnerFields method
     */
    public function testGetOwnerFields()
    {
        $user = $this->user_array['no_superuser_is_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $private_method_response = $this->invokeMethod($filter, 'getOwnerFields');
        $this->assertEquals([], $private_method_response);
    }

    /**
     * Test getBelongTo private method
     */
    public function testGetBelongTo()
    {
        $user = $this->user_array['no_superuser_is_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $private_method_response = $this->invokeMethod($filter, 'getBelongTo');
        $this->assertEquals([], $private_method_response);
    }

    /**
     * Test hasFullAccess private method
     */
    public function testHasFullAccess()
    {
        $user = $this->user_array['no_superuser_is_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $private_method_response = $this->invokeMethod($filter, 'hasFullAccess');
        $this->assertEquals(false, $private_method_response);
    }

    /**
     * Test getSupervisorWhereClause private method when user is not supervisor
     */
    public function testGetSupervisorWhereClauseIsNotSupervisor()
    {
        $user = $this->user_array['is_superuser_no_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $private_method_response = $this->invokeMethod($filter, 'getSupervisorWhereClause');
        $this->assertEquals([], $private_method_response);
    }

    /**
     * Test GetSupervisorWhereClause private method when the user is supervisor
     */
    public function testGetSupervisorWhereClauseIsSupervisor()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getParentJoinsWhereClause private method
     */
    public function testGetParentJoinsWhereClause()
    {
        $user = $this->user_array['is_superuser_no_supervisor'];
        $filter = new FilterQuery($this->Users->find(), $this->Users, $user);
        $private_method_response = $this->invokeMethod($filter, 'getParentJoinsWhereClause');
        $this->assertEquals([], $private_method_response);
    }

    /**
     * Test getWhereClause private method
     */
    public function testGetWhereClause()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
