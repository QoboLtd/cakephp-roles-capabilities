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

class NoAuthAccessTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.RolesCapabilities.Roles',
    ];

    /**
     * @var Table
     */
    private $Roles;

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
    }

    public function tearDown()
    {
        AuthorizationContextHolder::pop();
        EventManager::instance()->off($this->listener);
        parent::tearDown();
    }

    public function testAnonymousQuery(): void
    {
        $count = $this->Roles->find('all', ['fields' => 'id'])->count();
        $this->assertEquals($count, 0);
    }

    public function testSystemQuery(): void
    {
        AuthorizationContextHolder::asSystem();

        try {
            $count = $this->Roles->find('all', ['fields' => 'id'])->count();
        } finally {
            AuthorizationContextHolder::pop();
        }
        $this->assertGreaterThan(0, $count);
    }
}
