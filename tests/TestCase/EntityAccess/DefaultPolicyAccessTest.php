<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\EntityAccess\AuthorizationContext;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\Operation;
use RolesCapabilities\EntityAccess\UserWrapper;

class DefaultPolicyAccessTest extends TestCase
{
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
    private $Roles;

    /**
     * @var \Cake\ORM\Table
     */
    private $Users;

    /**
     * @var \Cake\ORM\Table
     */
    private $Groups;

    /**
     * @var \Cake\ORM\Table
     */
    private $GroupsUsers;

    public function setUp(): void
    {
        parent::setUp();

        AuthorizationContextHolder::push(AuthorizationContext::asAnonymous(null));

        $this->Roles = TableRegistry::getTableLocator()->get('RolesCapabilities.Roles');
        $this->Users = TableRegistry::getTableLocator()->get('RolesCapabilities.Users');
        $this->Groups = TableRegistry::getTableLocator()->get('Groups.Groups');
        $this->GroupsUsers = TableRegistry::getTableLocator()->get('Groups.GroupsUsers');

        $this->Users->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'Self' => [ 'association' => 'field', 'field' => 'id'],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'Self'],
            ],
        ]);
        $this->Groups->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'MemberOf' => [ 'association' => 'Users' ],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'MemberOf' ],
            ],
        ]);
        $this->GroupsUsers->addBehavior('RolesCapabilities.Authorized', [
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
        AuthorizationContextHolder::pop();
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

    public function testAnonymousQuery(): void
    {
        $count = $this->Roles->find()->count();
        $this->assertEquals($count, 0);
    }

    public function testSystemQuery(): void
    {
        AuthorizationContextHolder::asSystem();

        try {
            $count = $this->Roles->find()->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertGreaterThan(0, $count);
    }

    public function testViewSelf(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000003');

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $count = $this->Users->find()->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(1, $count);
    }

    public function testViewSelfById(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000003');

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $entity = $this->Users->get('00000000-0000-0000-0000-000000000003');
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertNotNull(1, $entity);
    }

    public function testTest(): void
    {
        AuthorizationContextHolder::asSystem();
        try {
            $q = $this->GroupsUsers->find()->where([ $this->GroupsUsers->aliasField('user_id') => '00000000-0000-0000-0000-000000000003']);
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(1, $count);
    }

    public function testViewGroupMembership(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000003');

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $q = $this->GroupsUsers->find();
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(1, $count);
    }

    public function testViewOwnGroup(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000003');

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $q = $this->Groups->find();
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(1, $count);
    }

    public function testViewOwnRole(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000003');

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $q = $this->Roles->find();
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(1, $count);
    }

    public function testSuperuserViewRoles(): void
    {
        AuthorizationContextHolder::asSystem();
        try {
            $user = $this->Users->find('all')->where([
                'is_superuser' => true,
            ])->first();
        } finally {
            AuthorizationContextHolder::pop();
        }

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $q = $this->Users->find();
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertGreaterThan(1, $count);
    }

    public function testUserAsSupervisor(): void
    {
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000002');

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $q = $this->Users->find();
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(3, $count);
    }
}
