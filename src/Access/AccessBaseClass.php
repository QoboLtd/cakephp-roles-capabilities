<?php

namespace RolesCapabilities\Access;

/**
 *  Base class for all check access rules classes
 *
 */
abstract class AccessBaseClass implements AccessInterface
{
    /**
     *  Abstract method to check user's access for specified URL
     *
     * @param array $url    URL client tries to access
     * @param array $user   user's session
     * @return bool         true in case of user has access to URL and false if not
     */
    abstract public function hasAccess($url, $user);
}
