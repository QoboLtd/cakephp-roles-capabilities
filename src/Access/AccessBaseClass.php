<?php

namespace RolesCapabilities\Access;

abstract class AccessBaseClass implements AccessInterface
{
    abstract public function hasAccess($url, $user);        
}
