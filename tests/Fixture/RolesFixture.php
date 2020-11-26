<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RolesFixture
 *
 */
class RolesFixture extends TestFixture
{
    public $table = 'qobo_roles';
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'description' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'deny_edit' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'deny_delete' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'UNIQUE_NAME' => ['type' => 'unique', 'columns' => ['name'], 'length' => []],
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
            'name' => 'Admins',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Administrators role',
            'deny_edit' => 1,
            'deny_delete' => 1,
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Everyone',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Generic role',
            'deny_edit' => 0,
            'deny_delete' => 1,
        ],
        [
            'id' => '79928943-0016-4677-869a-e37728ff6564',
            'name' => 'Supervisor',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Supervisor role',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ],
        [
            'id' => '79628943-0016-4677-869a-e37728ff6564',
            'name' => 'Sales',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Sales role',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ],

    ];
}
