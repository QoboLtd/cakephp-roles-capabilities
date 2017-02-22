<?php

namespace RolesCapabilities\CheckAccess;

/**
 *  AuthorizeCheckAccess Class
 *
 *  Check if user is authorized or not
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class AuthorizeCheckAccess extends BaseCheckAccess
{
    /**
     *  checkAccess impllementation for authorization
     *  checks
     *
     * @param array $url   URL user tries to access for
     * @param array $user  user's session data
     * @return true in case of authorized user and false if not
     */
    public function checkAccess($url, $user)
    {
        $result = false;
        if (!empty($user)) {
            $result = true;
        }

        return $result;
    }
}
