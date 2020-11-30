<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\EntityAccess;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\EntityAccess\AccessControlUtil;
use RolesCapabilities\EntityAccess\Operation;
use RolesCapabilities\EntityAccess\UserWrapper;

class AccessControlUtilTest extends TestCase
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
     * @var ?\Cake\ORM\Table
     */
    private $Users;

    public function setUp(): void
    {
        parent::setUp();

        $this->Users = TableRegistry::getTableLocator()->get('RolesCapabilities.Users');
        $this->Users->addBehavior('RolesCapabilities.Authorized', [
            'capabilities' => [
                ['operation' => Operation::VIEW, 'association' => 'All'],
            ],
        ]);
    }

    public function tearDown(): void
    {
        TableRegistry::clear();
        $this->Users = null;
        parent::tearDown();
    }

    public function testAnonymous(): void
    {
        $accessControl = new AccessControlUtil(null);

        $val = $accessControl->isAllowed($this->Users, 'view', null);

        $this->assertFalse($val, 'Anonymous access succeeded');
    }

    public function testUser(): void
    {
        $accessControl = new AccessControlUtil(UserWrapper::forUser(['id' => 'TEST']));

        $val = $accessControl->isAllowed($this->Users, 'view', null);

        $this->assertTrue($val, 'User access failed');
    }

    public function testViewAllowSelfById(): void
    {
        $accessControl = new AccessControlUtil(UserWrapper::forUser(['id' => '00000000-0000-0000-0000-000000000003']));

        $val = $accessControl->isAllowed($this->Users, 'view', '00000000-0000-0000-0000-000000000003');

        $this->assertTrue($val, 'User access failed');
    }

    public function testAnonymousWithId(): void
    {
        $accessControl = new AccessControlUtil(null);

        $val = $accessControl->isAllowed($this->Users, 'view', '00000000-0000-0000-0000-000000000003');

        $this->assertFalse($val, 'Anonymous access succeeded');
    }
}
