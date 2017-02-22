<?php

namespace RolesCapabilities\CheckAccess;

/**
 *  CheckAccessInterface
 *
 *  CheckAccessInterface defines methods all check access
 *  classes should implement
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
interface CheckAccessInterface
{
    /**
     *  checkAccess
     *
     * @param array $url   controller, action, parameteres
     * @param array $user  user info
     * @return true if access is granted and false in case of not
     */
    public function checkAccess($url, $user);
}
