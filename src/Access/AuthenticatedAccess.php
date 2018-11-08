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
 *  AuthenticatedAccess Class
 *
 *  Check if user is logged in or not
 *
 *  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *  NOTE: use that check very carefully! It gives full access to any authorized user!!!
 *  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class AuthenticatedAccess extends BaseAccessClass
{
    /**
     *  hasAccess impllementation for authentication
     *  checks
     *
     * @param mixed[] $url   URL user tries to access for
     * @param mixed[] $user  user's session data
     * @return bool true in case of authorized user and false if not
     */
    public function hasAccess(array $url, array $user): bool
    {
        if (!empty($user)) {
            return true;
        }

        return false;
    }
}
