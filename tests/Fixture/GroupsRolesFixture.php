<?php
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
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'group_id' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'role_id' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
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
    ];
}
