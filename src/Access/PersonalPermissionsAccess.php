<?php

namespace RolesCapabilities\Access;

use Cake\Core\App;
use Cake\ORM\TableRegistry;

/**
 *  PersonalPermissionsAccess class checks if user has personal access to specific entity
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class PersonalPermissionsAccess extends AuthenticatedAccess
{
    /**
     *  hasAccess method
     *
     * @param array $url    request URL
     * @param array $user   user's session data
     * @return bool         true or false
     */
    public function hasAccess()
    {
        $result = parent::hasAccess($url, $user);
        if (!$result) {
            return false;
        }

        $permissionTable = TableRegistry::get('RolesCapabilities.PersonalPermission');
        $query = $permissionTable->find('all', [
            'conditions' => [
                'model' => $url['controller'],
                'foreign_key' => $url['pass'][0],
                'type' => $url['action'],
                'user_id' => $user['id']
            ],
        ]);

        return $query->count() > 0 ? true : false;
    }
}
