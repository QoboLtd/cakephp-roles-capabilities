<?php

namespace RolesCapabilities\Access;

/**
 *  AccessInterface
 *
 *  AccessInterface defines methods all check access
 *  classes should implement
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
interface AccessInterface
{
    /**
     *  hasAccess
     *
     * @param array $url   controller, action, parameteres
     * @param array $user  user info
     * @return true if access is granted and false in case of not
     */
    public function hasAccess($url, $user);
}
