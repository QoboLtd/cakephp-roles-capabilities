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
        'id' => ['type' => 'uuid', 'null' => false ],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false ],
        'is_superuser' => ['type' => 'boolean', 'null' => false, 'default' => 0 ],
        'is_supervisor' => ['type' => 'boolean', 'null' => false, 'default' => 0 ],
        'reports_to' => ['type' => 'string', 'length' => 36, 'null' => true ],
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
        [
            'id' => '00000000-0000-0000-0000-000000000006',
            'name' => 'sales_user',
            'is_superuser' => false,
            'is_supervisor' => false,
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000007',
            'name' => 'sales_user_2',
            'is_superuser' => false,
            'is_supervisor' => false,
        ],
    ];
}
