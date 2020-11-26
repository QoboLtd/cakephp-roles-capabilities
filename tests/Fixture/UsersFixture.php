<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'is_superuser' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => 0, 'comment' => '', 'precision' => null],
        'is_supervisor' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => 0, 'comment' => '', 'precision' => null],
        'reports_to' => ['type' => 'string', 'length' => 36, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'primary_key' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
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
            'name' => 'superuser',
            'is_superuser' => true,
            'is_supervisor' => false,
            'reports_to' => null,
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'supervisor',
            'is_superuser' => false,
            'is_supervisor' => true,
            'reports_to' => null,
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'name' => 'user3',
            'is_superuser' => false,
            'is_supervisor' => false,
            'reports_to' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000004',
            'name' => 'user4',
            'is_superuser' => false,
            'is_supervisor' => false,
            'reports_to' => '00000000-0000-0000-0000-000000000002',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000005',
            'name' => 'no_roles_user',
            'is_superuser' => false,
            'is_supervisor' => false,
        ],
    ];
}
