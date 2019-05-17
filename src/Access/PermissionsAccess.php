<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace RolesCapabilities\Access;

use Cake\ORM\TableRegistry;
use RolesCapabilities\Model\Table\CapabilitiesTable;
use Webmozart\Assert\Assert;

/**
 *  PermissionsAccess class checks if user has access to specific entity
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class PermissionsAccess extends AuthenticatedAccess
{
    /**
     *  hasAccess method
     *
     * @param mixed[] $url    request URL
     * @param mixed[] $user   user's session data
     * @return bool         true or false
     */
    public function hasAccess(array $url, array $user): bool
    {
        $result = parent::hasAccess($url, $user);
        if (!$result) {
            return false;
        }
        //  permissions are assigned to the specified entity only!
        if (empty($url['pass'][0])) {
            return false;
        }

        $table = TableRegistry::get('RolesCapabilities.Capabilities');
        Assert::isInstanceOf($table, CapabilitiesTable::class);

        $groups = $table->getUserGroups($user['id']);

        $where = [
            'model' => $url['controller'], // WARNING: this might conflict with APP table's name matching a plugin's table name
            'type' => $url['action'],
            'foreign_key' => $url['pass'][0],
            'OR' => [
                ['owner_foreign_key' => $user['id'], 'owner_model' => 'Users']
            ]
        ];

        if (! empty($groups)) {
            $where['OR'][] = ['owner_foreign_key IN ' => array_keys($groups), 'owner_model' => 'Groups'];
        }

        $query = TableRegistry::get('RolesCapabilities.Permissions')
            ->find('all')
            ->select('foreign_key')
            ->where($where)
            ->applyOptions(['accessCheck' => false]);

        return $query->count() > 0 ? true : false;
    }
}
