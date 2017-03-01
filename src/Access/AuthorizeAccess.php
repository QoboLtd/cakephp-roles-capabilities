<?php

namespace RolesCapabilities\Access;

/**
 *  AuthorizeAccess Class
 *
 *  Check if user is authorized or not
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class AuthorizeAccess implements AccessInterface
{
    /**
     *  hasAccess impllementation for authorization
     *  checks
     *
     * @param array $url   URL user tries to access for
     * @param array $user  user's session data
     * @return true in case of authorized user and false if not
     */
    public function hasAccess($url, $user)
    {
        $result = false;
        if (empty($user)) {
            $result = true;
        }

        return $result;
    }
}
