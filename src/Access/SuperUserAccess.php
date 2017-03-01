<?php

namespace RolesCapabilities\Access;

/**
 *  SuperUserAccess Class
 *
 *  Check access for superuser
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class SuperUserAccess implements AccessInterface
{
    /**
     *  hasAccess for super user
     *
     * @param array $url    URL user tries to access for
     * @param array $user   user's session data
     * @return true in case of superuser and false if not
     */
    public function hasAccess($url, $user)
    {
        $result = false;
        if (!empty($user['is_superuser']) && $user['is_superuser']) {
            $result = true;
        }

        return $result;
    }
}
