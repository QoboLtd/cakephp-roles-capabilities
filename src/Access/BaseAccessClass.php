<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace RolesCapabilities\Access;

/**
 *  Base class for all check access rules classes
 */
abstract class BaseAccessClass implements AccessInterface
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
