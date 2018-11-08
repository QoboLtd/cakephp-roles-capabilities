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
     * @param mixed[] $url   controller, action, parameteres
     * @param mixed[] $user  user info
     * @return bool true if access is granted and false in case of not
     */
    public function hasAccess(array $url, array $user): bool;
}
