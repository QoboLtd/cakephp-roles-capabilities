<?php

namespace RolesCapabilities\CheckAccess;

/**
 *  BaseCheckAccess Class
 *
 *  Base class for checking of user's access rights
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class BaseCheckAccess implements CheckAccessInterface
{
    /**
     *  checkAccess
     *
     *  Implement basic logic to check user's access
     *
     * @param array $url    URL user tries to access for
     * @param array $user   user's session data
     * @return void
     */
    public function checkAccess($url, $user)
    {
        $result = false;
    }
}
