<?php
namespace RolesCapabilities\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PermissionsFixture
 *
 */
class PermissionsFixture extends TestFixture
{
    public $table = 'qobo_permissions';
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'foreign_key' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'model' => ['type' => 'string', 'length' => 128, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'owner_foreign_key' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'owner_model' => ['type' => 'string', 'length' => 128, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'creator' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'type' => ['type' => 'string', 'length' => 15, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'expired' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
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
            'foreign_key' => 'c4bd0658-f0d8-482b-bf02-4ffe45f18bdf',
            'model' => 'Leads',
            'owner_foreign_key' => '00000000-0000-0000-0000-000000000003',
            'owner_model' => 'Users',
            'creator' => '00000000-0000-0000-0000-000000000001',
            'type' => 'view',
            'expired' => null,
            'created' => '2017-04-12 11:21:52',
            'modified' => '2017-04-12 11:21:52'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'foreign_key' => '00000000-0000-0000-0000-000000000002',
            'model' => 'Leads',
            'owner_foreign_key' => '00000000-0000-0000-0000-000000000003',
            'owner_model' => 'Users',
            'creator' => '00000000-0000-0000-0000-000000000001',
            'type' => 'view',
            'expired' => null,
            'created' => '2019-08-28 11:45:12',
            'modified' => '2019-08-28 11:45:12'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'foreign_key' => '00000000-0000-0000-0000-000000000003',
            'model' => 'Articles',
            'owner_foreign_key' => '00000000-0000-0000-0000-000000000003',
            'owner_model' => 'Users',
            'creator' => '00000000-0000-0000-0000-000000000001',
            'type' => 'view',
            'expired' => null,
            'created' => '2019-08-28 11:45:12',
            'modified' => '2019-08-28 11:45:12'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'foreign_key' => 'c4bd0658-f0d8-482b-bf02-4ffe45f18bdf',
            'model' => 'Leads',
            'owner_foreign_key' => '00000000-0000-0000-0000-000000000003',
            'owner_model' => 'Groups',
            'creator' => '00000000-0000-0000-0000-000000000001',
            'type' => 'view',
            'expired' => null,
            'created' => '2019-08-28 11:45:12',
            'modified' => '2019-08-28 11:45:12'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000005',
            'foreign_key' => 'c4bd0658-f0d8-482b-bf02-4ffe45f18bdf',
            'model' => 'Leads',
            'owner_foreign_key' => '00000000-0000-0000-0000-000000000002',
            'owner_model' => 'Users',
            'creator' => '00000000-0000-0000-0000-000000000001',
            'type' => 'view',
            'expired' => null,
            'created' => '2017-04-12 11:21:52',
            'modified' => '2017-04-12 11:21:52'
        ],
    ];
}
