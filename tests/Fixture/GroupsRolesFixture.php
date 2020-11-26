<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GroupsRolesFixture
 *
 */
class GroupsRolesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'null' => false ],
        'group_id' => ['type' => 'string', 'length' => 36, 'null' => false ],
        'role_id' => ['type' => 'string', 'length' => 36, 'null' => false ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
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
            'id' => '00000000-0000-0000-0000-000000000001',
            'group_id' => '959b9de4-07c7-4032-a9a0-0d075ca2c633',
            'role_id' => '79928943-0016-4677-869a-e37728ff6564',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'group_id' => '00000000-0000-0000-0000-000000000002',
            'role_id' => '00000000-0000-0000-0000-000000000001',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'group_id' => '00000000-0000-0000-0000-000000000003',
            'role_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'group_id' => '959b9de4-07c7-4032-a9a0-0d075ca2c633',
            'role_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000005',
            'group_id' => '969b9de4-07c7-4032-a9a0-0d075ca2c633',
            'role_id' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '79628943-0016-4677-869a-e37728ff6564',
            'group_id' => '79628943-0016-4677-869a-e37728ff6564',
            'role_id' => '79628943-0016-4677-869a-e37728ff6564',
        ],
    ];
}
