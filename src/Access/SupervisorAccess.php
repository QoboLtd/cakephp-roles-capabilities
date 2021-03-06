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

/**
 *  SupervisorAccess Class
 *
 *  Check access for superviser
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class SupervisorAccess extends AuthenticatedAccess
{
    /**
     *  hasAccess for superviser user
     *
     * @param mixed[] $url    URL user tries to access for
     * @param mixed[] $user   user's session data
     * @return bool true in case of superuser and false if not
     */
    public function hasAccess(array $url, array $user): bool
    {
        $result = parent::hasAccess($url, $user);

        if (!$result) {
            return $result;
        }

        if (!empty($user['is_supervisor']) && $user['is_supervisor']) {
            $users = Utils::getReportToUsers($user['id']);

            foreach ($users as $userRecord) {
                $result = (new AccessFactory())->hasAccess($url, $userRecord->toArray());

                if ($result) {
                    return $result;
                }
            }
        }

        return false;
    }
}
