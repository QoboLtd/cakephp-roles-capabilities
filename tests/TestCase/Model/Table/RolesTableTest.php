<?php
namespace RolesCapabilities\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Model\Table\RolesTable;
use Webmozart\Assert\Assert;

/**
 * RolesCapabilities\Model\Table\RolesTable Test Case
 *
 * @property \RolesCapabilities\Model\Table\RolesTable $Roles
 */
class RolesTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.roles_capabilities.roles',
        'plugin.roles_capabilities.capabilities',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Roles') ? [] : ['className' => 'RolesCapabilities\Model\Table\RolesTable'];
        /**
         * @var \RolesCapabilities\Model\Table\RolesTable $table
         */
        $table = TableRegistry::get('Roles', $config);
        $this->Roles = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Roles);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals($this->Roles->getTable(), 'qobo_roles', 'Table name');
        $this->assertEquals($this->Roles->getDisplayField(), 'name', 'Display field');
        $this->assertEquals($this->Roles->getPrimaryKey(), 'id', 'Primary key');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $role = $this->Roles->newEntity([
            'name' => 'test',
        ]);
        $this->assertCount(0, $role->getErrors(), 'No errors');
        $role = $this->Roles->newEntity([
            'created' => date('Y-m-d H:i:s'),
        ]);
        $this->assertArraySubset([
            'name' => [
                '_required' => 'This field is required',
            ],
        ], $role->getErrors(), true, 'Missing required property *name* error');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $role1 = $this->Roles->newEntity([
            'name' => 'test',
            'description' => 'Test description',
            'deny_edit' => false,
            'deny_delete' => false,
        ]);
        $this->Roles->save($role1);

        $role2 = $this->Roles->newEntity([
            'name' => 'test',
        ]);
        $this->assertArraySubset([
            'name' => [
                'unique' => 'The provided value is invalid',
            ],
        ], $role2->getErrors(), true, 'Non unique role name');

        $role1 = $this->Roles->patchEntity($role1, ['description' => 'New description']);
        $this->Roles->save($role1);
        $this->assertArraySubset([], $role1->getErrors(), true, 'Non editable entity');
    }

    public function testSave(): void
    {
        $data = ['name' => 'Foobar', 'description' => 'Foobar role', 'deny_edit' => false, 'deny_delete' => false];

        $entity = $this->Roles->newEntity($data);
        $this->assertInstanceOf(\RolesCapabilities\Model\Entity\Role::class, $this->Roles->save($entity));
        $this->assertNotEmpty($entity->get('id'));
    }

    public function testSaveFromAllowEditToDenyEdit(): void
    {
        $this->Roles->deleteAll([]);

        $data = [
            'name' => 'Allow Edit',
            'description' => 'Allow Edit description',
            'deny_edit' => false, // allow edit initially
            'deny_delete' => true,
        ];

        $entity = $this->Roles->newEntity($data);
        $this->Roles->save($entity);

        // switched to deny edit
        $data['deny_edit'] = true;
        $this->Roles->patchEntity($entity, $data);
        $this->Roles->save($entity);

        $entity = $this->Roles->find()->where(['name' => $data['name']])->firstOrFail();
        Assert::isInstanceOf($entity, \Cake\Datasource\EntityInterface::class);

        $this->assertSame([], array_diff_assoc(['deny_edit' => true], $entity->toArray()));
    }

    public function testSaveFromDenyEditToAllowEdit(): void
    {
        $this->Roles->deleteAll([]);

        $data = [
            'name' => 'Deny Edit',
            'description' => 'Deny Edit description',
            'deny_edit' => true, // deny edit initially
            'deny_delete' => true,
        ];

        $entity = $this->Roles->newEntity($data);
        $this->Roles->save($entity);

        // switched to allow edit
        $data['deny_edit'] = false;
        $this->Roles->patchEntity($entity, $data);
        $this->Roles->save($entity);

        $entity = $this->Roles->find()->where(['name' => $data['name']])->firstOrFail();
        Assert::isInstanceOf($entity, \Cake\Datasource\EntityInterface::class);

        $this->assertSame([], array_diff_assoc(['deny_edit' => true], $entity->toArray()));
    }

    public function testDeleteWithAllowDelete(): void
    {
        $this->Roles->deleteAll([]);

        $data = [
            'name' => 'Deny Edit',
            'description' => 'Deny Edit description',
            'deny_edit' => false,
            'deny_delete' => false,
        ];

        $entity = $this->Roles->newEntity($data);
        $this->Roles->save($entity);

        $entity = $this->Roles->find()->where(['name' => $data['name']])->firstOrFail();
        Assert::isInstanceOf($entity, \Cake\Datasource\EntityInterface::class);

        $this->assertTrue($this->Roles->delete($entity));
    }

    public function testDeleteWithDenyDelete(): void
    {
        $this->Roles->deleteAll([]);

        $data = [
            'name' => 'Deny Edit',
            'description' => 'Deny Edit description',
            'deny_edit' => false,
            'deny_delete' => true,
        ];

        $entity = $this->Roles->newEntity($data);
        $this->Roles->save($entity);

        $this->assertFalse($this->Roles->delete($entity));
    }

    public function testPrepareCapabilities(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
