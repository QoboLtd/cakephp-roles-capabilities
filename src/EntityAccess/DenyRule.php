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
namespace RolesCapabilities\EntityAccess;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\ORM\Table;

/**
 *  Always deny.
 */
class DenyRule implements AuthorizationRule
{
    /**
     * @inheritdoc
     */
    public function allow(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function expression(Query $query): QueryExpression
    {
        return $query->newExpr('0=1');
    }
}