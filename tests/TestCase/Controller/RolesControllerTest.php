<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\Controller;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\Operation;

/**
 * RolesCapabilities\Controller\RolesController Test Case
 */
class RolesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.RolesCapabilities.ExtendedCapabilities',
        'plugin.RolesCapabilities.Groups',
        'plugin.RolesCapabilities.GroupsRoles',
        'plugin.RolesCapabilities.GroupsUsers',
        'plugin.RolesCapabilities.Permissions',
        'plugin.RolesCapabilities.Roles',
        'plugin.RolesCapabilities.Users',
    ];

    /**
     * @var \Cake\ORM\Table
     */
    private $Users;

    /**
     * @var \Cake\ORM\Table
     */
    private $Roles;

    /**
     * @var \Cake\ORM\Table
     */
    private $ExtendedCapabilities;

    public function setUp(): void
    {
        parent::setUp();

        $locator = TableRegistry::getTableLocator();

        $this->Users = $locator->get('RolesCapabilities.Users');

        $this->Users->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'Self' => [ 'association' => 'field', 'field' => 'id'],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'Self'],
            ],
        ]);

        $this->Roles = $locator->get('RolesCapabilities.Roles');
        $this->ExtendedCapabilities = $locator->get('RolesCapabilities.ExtendedCapabilities');
    }

    public function tearDown(): void
    {
        TableRegistry::clear();
        parent::tearDown();
    }

    private function fetchUser(string $id): EntityInterface
    {
        AuthorizationContextHolder::asSystem();
        try {
            return $this->Users->get($id);
        } finally {
            AuthorizationContextHolder::pop();
        }
    }

    private function fetchRole(string $name): ?EntityInterface
    {
        AuthorizationContextHolder::asSystem();
        try {
            return $this->Roles->find('all')->where(['name' => '__Test_Role__'])->first();
        } finally {
            AuthorizationContextHolder::pop();
        }
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndexUser(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000005');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->get('/roles-capabilities/roles/index');
        $this->assertResponseCode(403);
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndexAdmin(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->get('/roles-capabilities/roles/index');
        $this->assertResponseCode(200);
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->get('/roles-capabilities/roles/view/00000000-0000-0000-0000-000000000001');
        $this->assertResponseCode(200);
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->configRequest([
            'environment' => [
                'HTTP_REFERER' => '/roles-capabilities/roles',
            ],
        ]);

        $role = [
            'name' => '__Test_Role__',
            'description' => 'Test Role Description',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ];

        $this->post('/roles-capabilities/roles/add', $role);
        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);

        $role = $this->fetchRole('__Test_Role__');
        $this->assertNotNull($role, 'Role not found in database');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->configRequest([
            'environment' => [
                'HTTP_REFERER' => '/roles-capabilities/roles',
            ],
        ]);

        $roleData = [
            'name' => '__Test_Role__',
            'description' => 'Test Role Description',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ];

        $this->post('/roles-capabilities/roles/add', $roleData);
        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);
        AuthorizationContextHolder::pop();

        $role = $this->fetchRole('__Test_Role__');
        $this->assertNotNull($role, 'Role not found in database');
        $roleId = $role['id'];

        if ($roleId === null) {
            return;
        }

        $this->get('/roles-capabilities/roles/view/' . $roleId);
        $this->assertResponseCode(200);
        AuthorizationContextHolder::pop();

        $this->configRequest([
            'environment' => [
                'HTTP_REFERER' => '/roles-capabilities/roles',
            ],
        ]);
        $this->post('/roles-capabilities/roles/edit/' . $roleId, $roleData);
        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEditWithCapabilities(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->configRequest([
            'environment' => [
                'HTTP_REFERER' => '/roles-capabilities/roles',
            ],
        ]);

        $roleData = [
            'name' => '__Test_Role__',
            'description' => 'Test Role Description',
            'deny_edit' => 0,
            'deny_delete' => 0,
            'capabilities' => json_encode(
                [
                    [ 'resource' => 'MY_TEST_RESOURCE', 'operation' => 'view', 'association' => 'All' ],
                ],
            ),
        ];

        $this->post('/roles-capabilities/roles/add', $roleData);
        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);

        $role = $this->fetchRole('__Test_Role__');
        $this->assertNotNull($role, 'Role not found in database');
        $roleId = $role['id'];

        if ($roleId === null) {
            return;
        }

        $cap = $this->ExtendedCapabilities->find()->where(['role_id' => $roleId ])->first();

        $this->assertEquals($cap['resource'], 'MY_TEST_RESOURCE');
        $this->assertEquals($cap['operation'], 'view');
        $this->assertEquals($cap['association'], 'All');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->configRequest([
            'environment' => [
                'HTTP_REFERER' => '/roles-capabilities/roles',
            ],
        ]);

        $this->post('/roles-capabilities/roles/add', [
            'name' => '__Test_Role__',
            'description' => 'Test Role Description',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ]);
        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);
        AuthorizationContextHolder::pop();

        $role = $this->fetchRole('__Test_Role__');

        $this->configRequest([
            'environment' => [
                'HTTP_REFERER' => '/roles-capabilities/roles',
            ],
        ]);
        $this->delete('/roles-capabilities/roles/delete/' . $role['id']);

        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);
    }
}
