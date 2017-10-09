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
