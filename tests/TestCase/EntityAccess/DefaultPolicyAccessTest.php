<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Http\ServerRequest;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Access\NoAuthAccess;
use RolesCapabilities\EntityAccess\AuthorizationContext;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\Event\QueryFilterEventsListener;
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
     * @var Table
     */
    private $Roles;

    private $Users;

    private $Groups;

    private $GroupsUsers;

    /**
     * @var QueryFilterEventsListener
     */
    private $listener;

    public function setUp(): void
    {
        parent::setUp();

        AuthorizationContextHolder::push(AuthorizationContext::asAnonymous(null));

        $this->Roles = TableRegistry::getTableLocator()->get('RolesCapabilities.Roles');
        $this->Users = TableRegistry::getTableLocator()->get('RolesCapabilities.Users');
        $this->Groups = TableRegistry::getTableLocator()->get('Groups.Groups');
        $this->GroupsUsers = TableRegistry::getTableLocator()->get('Groups.GroupsUsers');

        $this->Users->addBehavior('RolesCapabilities.Authorized');
        $this->Groups->addBehavior('RolesCapabilities.Authorized');
        $this->GroupsUsers->addBehavior('RolesCapabilities.Authorized');
    }

    public function tearDown(): void
    {
        TableRegistry::clear();
        AuthorizationContextHolder::pop();
        parent::tearDown();
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
        AuthorizationContextHolder::asSystem();
        try {
            $user = $this->Users->find()->where([
                'name' => 'user3',
            ])->first()->toArray();
        } finally {
            AuthorizationContextHolder::pop();
        }

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $count = $this->Users->find()->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(1, $count);
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
        AuthorizationContextHolder::asSystem();
        try {
            $user = $this->Users->find()->where([
                'id' => '00000000-0000-0000-0000-000000000003',
            ])->first()->toArray();
        } finally {
            AuthorizationContextHolder::pop();
        }

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
        AuthorizationContextHolder::asSystem();
        try {
            $user = $this->Users->find()->where([
                'id' => '00000000-0000-0000-0000-000000000003',
            ])->first()->toArray();
        } finally {
            AuthorizationContextHolder::pop();
        }

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
        AuthorizationContextHolder::asSystem();
        try {
            $user = $this->Users->find()->where([
                'id' => '00000000-0000-0000-0000-000000000003',
            ])->first()->toArray();
        } finally {
            AuthorizationContextHolder::pop();
        }

        AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), null));
        try {
            $q = $this->Roles->find();
            $count = $q->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertEquals(1, $count);
    }
}
