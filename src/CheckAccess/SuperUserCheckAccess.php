<?php

namespace RolesCapabilities\CheckAccess;

/**
 *  SuperUserCheckAccess Class
 *
 *  Check access for superuser
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class SuperUserCheckAccess implements CheckAccessInterface
{
    /**
     *  checkAccess for super user
     *
     * @param array $url    URL user tries to access for
     * @param array $user   user's session data
     * @return true in case of superuser and false if not
     */
    public function checkAccess($url, $user)
    {
        $result = false;
        // superuser has access everywhere
        if (!empty($user['is_superuser']) && $user['is_superuser']) {
            $result = true;
        }

        return $result;
    }
}
