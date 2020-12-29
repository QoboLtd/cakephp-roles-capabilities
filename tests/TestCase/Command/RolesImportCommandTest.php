<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\TestCase\Command;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use RolesCapabilities\Command\RolesImportCommand;
use Webmozart\Assert\Assert;

class RolesImportCommandTest extends TestCase
{
    public $fixtures = [
        'plugin.Groups.Groups',
        'plugin.RolesCapabilities.GroupsRoles',
        'plugin.RolesCapabilities.Roles',
    ];

    /**
     * @var \Cake\Console\ConsoleIo
     */
    private $io;

    /**
     * @var \Cake\ORM\Table
     */
    private $table;

    public function setUp()
    {
        parent::setUp();

        $this->table = $this->getTableLocator()->get('RolesCapabilities.Roles');

        /** @var \Cake\Console\ConsoleIo */
        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();
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

        $command = new RolesImportCommand();
        $command->run([], $this->io);

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

        $command->run([], $this->io);

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
