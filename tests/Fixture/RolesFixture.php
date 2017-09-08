<?php
namespace RolesCapabilities\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RolesFixture
 *
 */
class RolesFixture extends TestFixture
{

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
            'name' => 'Admins',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Administrators role',
            'deny_edit' => 1,
            'deny_delete' => 1
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Everyone',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Generic role',
            'deny_edit' => 0,
            'deny_delete' => 1
        ],
        [
            'id' => '79928943-0016-4677-869a-e37728ff6564',
            'name' => 'Lorem ipsum dolor sit amet',
            'created' => '2016-02-09 13:35:11',
            'modified' => '2016-02-09 13:35:11',
            'trashed' => null,
            'description' => 'Lorem ipsum dolor sit amet',
            'deny_edit' => 0,
            'deny_delete' => 0
        ],
    ];
}
