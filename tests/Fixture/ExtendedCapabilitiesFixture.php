<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CapabilitiesFixture
 *
 */
class ExtendedCapabilitiesFixture extends TestFixture
{
    public $table = 'qobo_extended_capabilities';
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'null' => false ],
        'role_id' => ['type' => 'string', 'length' => 36, 'null' => false ],
        'resource' => ['type' => 'string', 'length' => 255, 'null' => false ],
        'association' => ['type' => 'string', 'length' => 255, 'null' => false ],
        'operation' => ['type' => 'string', 'length' => 255, 'null' => false ],
        
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ]
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 'b48036a7-0b7f-4989-bd73-ecad4c20bc81',
            'role_id' => '79628943-0016-4677-869a-e37728ff6564',
            'resource' => 'RolesCapabilities.Users',
            'operation' => 'view',
            'association' => 'SameGroup',
        ],
    ];
}
