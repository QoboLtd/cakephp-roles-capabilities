<?php
declare(strict_types=1);

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
     * @var mixed[]
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'description' => ['type' => 'string', 'length' => 255, 'null' => false],
        'remote_group_id' => ['type' => 'string', 'length' => 255, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        'deny_delete' => ['type' => 'boolean', 'null' => false],
        'deny_edit' => ['type' => 'boolean', 'null' => false],
        'trashed' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var mixed[]
     */
    public $records = [
        [
            'id' => '959b9de4-07c7-4032-a9a0-0d075ca2c633',
            'name' => 'Lorem ipsum dolor sit amet',
            'remote_group_id' => null,
            'created' => '2016-02-04 11:12:29',
            'modified' => '2016-02-04 11:12:29',
            'trashed' => null,
            'description' => 'Lorem ipsum dolor sit amet',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ],
        [
            'id' => '969b9de4-07c7-4032-a9a0-0d075ca2c633',
            'name' => 'Test group',
            'remote_group_id' => null,
            'created' => '2016-02-04 11:12:29',
            'modified' => '2016-02-04 11:12:29',
            'trashed' => null,
            'description' => 'Test group',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ],
        [
            'id' => '79628943-0016-4677-869a-e37728ff6564',
            'name' => 'Sales Group',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Sales group',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ],
    ];
}
