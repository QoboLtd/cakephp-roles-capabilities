<?php
namespace RolesCapabilities\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GroupsFixture
 *
 */
class GroupsFixture extends TestFixture
{
    public $table = 'qobo_groups';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'remote_group_id' => ['type' => 'string', 'length' => 255, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        'trashed' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '959b9de4-07c7-4032-a9a0-0d075ca2c633',
            'name' => 'Lorem ipsum dolor sit amet',
            'remote_group_id' => null,
            'created' => '2016-02-04 11:12:29',
            'modified' => '2016-02-04 11:12:29',
            'trashed' => null,
        ],
        [
            'id' => '969b9de4-07c7-4032-a9a0-0d075ca2c633',
            'name' => 'Test group',
            'remote_group_id' => null,
            'created' => '2016-02-04 11:12:29',
            'modified' => '2016-02-04 11:12:29',
            'trashed' => null,
        ],
    ];
}
