<?php
declare(strict_types=1);

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
     * @var mixed[]
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'null' => false ],
        'foreign_key' => ['type' => 'uuid', 'null' => false ],
        'model' => ['type' => 'string', 'length' => 128, 'null' => false ],
        'owner_foreign_key' => ['type' => 'uuid', 'null' => false ],
        'owner_model' => ['type' => 'string', 'length' => 128, 'null' => false ],
        'creator' => ['type' => 'string', 'length' => 36, 'null' => false ],
        'type' => ['type' => 'string', 'length' => 15, 'null' => false ],
        'expired' => ['type' => 'datetime', 'null' => true ],
        'created' => ['type' => 'datetime', 'null' => false ],
        'modified' => ['type' => 'datetime', 'null' => false ],
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
            'id' => '00000000-0000-0000-0000-000000000001',
            'foreign_key' => 'c4bd0658-f0d8-482b-bf02-4ffe45f18bdf',
            'model' => 'Leads',
            'owner_foreign_key' => '00000000-0000-0000-0000-000000000003',
            'owner_model' => 'Users',
            'creator' => '00000000-0000-0000-0000-000000000001',
            'type' => 'view',
            'expired' => null,
            'created' => '2017-04-12 11:21:52',
            'modified' => '2017-04-12 11:21:52',
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
            'modified' => '2019-08-28 11:45:12',
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
            'modified' => '2019-08-28 11:45:12',
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
            'modified' => '2019-08-28 11:45:12',
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
            'modified' => '2017-04-12 11:21:52',
        ],
    ];
}
