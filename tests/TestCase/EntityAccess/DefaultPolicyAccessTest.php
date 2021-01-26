<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\Datasource\EntityInterface;
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
     * @var array<string>
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

        AuthorizationContextHolder::push(AuthorizationContext::asAnonymous());

        $this->Roles = $this->getTableLocator()->get('RolesCapabilities.Roles');
        $this->Users = $this->getTableLocator()->get('RolesCapabilities.Users');
        $this->Groups = $this->getTableLocator()->get('Groups.Groups');
        $this->GroupsUsers = $this->getTableLocator()->get('Groups.GroupsUsers');

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

        $this->Roles->addBehavior('RolesCapabilities.Authorized', [
            'associations' => [
                'AssignedRoles' => [ 'association' => 'Groups.Users' ],
            ],
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'AssignedRoles' ],
            ],
        ]);
    }

    public function tearDown(): void
    {
        $this->getTableLocator()->clear();
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

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user)));
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

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user)));
        try {
            $entity = $this->Users->get('00000000-0000-0000-0000-000000000003');
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertNotNull($entity, 'Entity access not allowed');
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

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user)));
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

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user)));
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

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user)));
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
        $user = $this->fetchUser('00000000-0000-0000-0000-000000000001');

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user)));
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

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user)));
        try {
            $q = $this->Users->find();
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(3, $count);
    }
}
