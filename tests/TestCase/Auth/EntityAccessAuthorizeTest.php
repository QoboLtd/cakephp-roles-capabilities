<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\Auth;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\Operation;

class EntityAccessAuthorizeTest extends TestCase
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
        $locator->get('Groups.Groups')->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'MemberOf' => [ 'association' => 'Users' ],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'MemberOf' ],
            ],
        ]);
        $locator->get('Groups.GroupsUsers')->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'Membership' => [ 'association' => 'field', 'field' => 'user_id' ],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'Membership' ],
            ],
        ]);
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

    public function testAnonymous(): void
    {
         $this->get('/roles-capabilities/roles');

        $this->assertResponseCode(302);
    }

    public function testNonAdminWithRoles(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000003');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->get('/roles-capabilities/roles');

        $this->assertResponseCode(200);
    }

    public function testNonAdminWithoutRoles(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000004');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->get('/roles-capabilities/roles');

        $this->assertResponseCode(403);
    }

    public function testAdminWithRoles(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        $this->session([
            'Auth' => [
                'User' => $user->toArray(),
            ],
        ]);

        $this->get('/roles-capabilities/roles');

        $this->assertResponseCode(200);
    }
}
