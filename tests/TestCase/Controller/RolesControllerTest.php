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
    }

    public function tearDown(): void
    {
        AuthorizationContextHolder::pop();
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
            'id' => '34adbb61-caf9-4cf3-97e7-b063fa0cd130',
            'name' => 'Test Role',
            'description' => 'Test Role Description',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ];

        $this->post('/roles-capabilities/roles/add', $role);

        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
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

        $this->delete('/roles-capabilities/roles/delete/00000000-0000-0000-0000-000000000002');

        $this->assertRedirect(['controller' => 'Roles', 'action' => 'index']);
    }
}
