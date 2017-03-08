<?php

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
class AuthenticatedAccess extends NoAuthAccess
{
    /**
     *  hasAccess impllementation for authentication
     *  checks
     *
     * @param array $url   URL user tries to access for
     * @param array $user  user's session data
     * @return true in case of authorized user and false if not
     */
    public function hasAccess($url, $user)
    {
        $result = parent::hasAccess($url, $user);
        if ($result) {
            return $result;
        }
        if (!empty($user)) {
            $result = true;
        }

        return $result;
    }
}
