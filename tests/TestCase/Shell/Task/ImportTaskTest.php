<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\Shell\Task;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Shell\Task\ImportTask;
use Webmozart\Assert\Assert;

class ImportTaskTest extends TestCase
{
    public $fixtures = [
        'plugin.Groups.Groups',
        'plugin.RolesCapabilities.GroupsRoles',
        'plugin.RolesCapabilities.Roles',
    ];

    /**
     * @var \RolesCapabilities\Shell\Task\ImportTask
     */
    private $task;

    /**
     * @var \Cake\ORM\Table
     */
    private $table;

    public function setUp()
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('RolesCapabilities.Roles');

        /** @var \Cake\Console\ConsoleIo */
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();

        $this->task = new ImportTask($io);
    }

    public function tearDown()
    {
        unset($this->table);
        unset($this->task);

        parent::tearDown();
    }

    /**
     * @dataProvider rolesProvider
     * @param mixed[] $data Role data
     */
    public function testMain(array $data): void
    {
        $this->table->deleteAll([]);

        $this->task->main();

        $query = $this->table->find()->where(['name' => $data['name']]);
        $this->assertSame(1, $query->count());

        $entity = $query->firstOrFail();
        Assert::isInstanceOf($entity, \Cake\Datasource\EntityInterface::class);
        $role = $entity->toArray();

        $this->assertSame([], array_diff_assoc($data, $role));
        $initialModifiedDate = $role['modified'];

        $this->table->updateAll(['description' => 'Some random description ' . uniqid()], []);

        // sleeping so we can capture the modified time diff.
        sleep(1);

        $this->task->main();

        $entity = $this->table->find()->where(['name' => $data['name']])->firstOrFail();
        Assert::isInstanceOf($entity, \Cake\Datasource\EntityInterface::class);
        $updated = $entity->toArray();

        $data['deny_edit'] ?
            $this->assertTrue($updated['modified']->getTimestamp() === $initialModifiedDate->getTimestamp()) :
            $this->assertTrue($updated['modified']->getTimestamp() > $initialModifiedDate->getTimestamp());

        unset($data['description']);
        $this->assertSame([], array_diff_assoc($data, $updated));
    }

    /**
     * @return mixed[]
     */
    public function rolesProvider(): array
    {
        $roles = [];
        foreach (Configure::read('RolesCapabilities.Roles') as $role) {
            $roles[] = [$role];
        }

        return $roles;
    }
}
