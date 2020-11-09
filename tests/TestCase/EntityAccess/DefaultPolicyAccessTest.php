<?php

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\Core\Configure;
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

    /**
     * @var QueryFilterEventsListener
     */
    private $listener;

    public function setUp()
    {
        parent::setUp();
        AuthorizationContextHolder::push(AuthorizationContext::asAnonymous(null));

        $this->listener = new QueryFilterEventsListener();
        EventManager::instance()->on($this->listener);

        $this->Roles = TableRegistry::getTableLocator()->get('RolesCapabilities.Roles');
        $this->Users = TableRegistry::getTableLocator()->get('RolesCapabilities.Users');
    }

    public function tearDown()
    {
        AuthorizationContextHolder::pop();
        EventManager::instance()->off($this->listener);
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
                'is_superuser' => false,
                'is_supervisor' => false,
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
}
