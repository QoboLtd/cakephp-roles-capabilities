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

use Cake\Core\App;
use Cake\ORM\TableRegistry;

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
     * @param array $url    request URL
     * @param array $user   user's session data
     * @return bool         true or false
     */
    public function hasAccess($url, $user)
    {
        $result = parent::hasAccess($url, $user);
        if (!$result) {
            return false;
        }
        //  permissions are assigned to the specified entity only!
        if (empty($url['pass'][0])) {
            return false;
        }

        $groups = TableRegistry::get('RolesCapabilities.Capabilities')->getUserGroups($user['id']);
        $query = TableRegistry::get('RolesCapabilities.Permissions')
            ->find('all')
            ->select('foreign_key')
            ->where([
                'model' => $url['controller'],
                'type IN ' => $url['action'],
                'foreign_key' => $url['pass'][0],
                'OR' => [
                            [
                                'owner_foreign_key IN ' => array_keys($groups),
                                'owner_model' => 'Groups',
                            ],
                            [
                                'owner_foreign_key' => $user['id'],
                                'owner_model' => 'Users',
                            ]
                    ]
            ])
            ->applyOptions(['accessCheck' => false]);

        return $query->count() > 0 ? true : false;
    }
}
