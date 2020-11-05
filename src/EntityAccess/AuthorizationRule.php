<?php
declare(strict_types=1);
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
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;

/**
 *  AuthorizationRule
 *
 * @author Nicos Panayides
 */
interface AuthorizationRule
{
    /**
     * Checks if the action is authorized
     *
     * @return bool Whether the action is allowed
     */
    public function allow(): bool;

    /**
     * Converts this rule to a query expression. Applied for "view" and "index"
     *
     * @param Query $query The query to build the expression.
     *
     * @return QueryExpression query expression to apply on query.
     */
    public function expression(Query $query): ?QueryExpression;
}
