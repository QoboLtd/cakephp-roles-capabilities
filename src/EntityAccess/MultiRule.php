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

use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use RuntimeException;

/**
 *
 * Multiple rule authorization.
 *
 * @author Nicos Panayides <n.panayides@qobogroup.com>
 */
class MultiRule implements AuthorizationRule
{
    /**
     *  List of rules
     *
     * @var AuthorizationRule[]
     */
    protected $rules = [];

    /**
     * @var string
     */
    protected $conjunction;

    /**
     *  Constructor. Use one of ::any, ::all
     *
     * @param string $conjunction The conjunction (ie. AND, OR)
     * @param AuthorizationRule ...$rules List of rules
     */
    private function __construct(string $conjunction, AuthorizationRule ...$rules)
    {
        $this->rules = $rules;
        $this->conjunction = $conjunction;
    }

    /**
     * Any rule must match
     *
     * @param AuthorizationRule ...$rules The rules
     * @return MultiRule A rule that combines all rules.
     */
    public static function any(AuthorizationRule ...$rules): MultiRule
    {
        return new MultiRule('OR', ...$rules);
    }

    /**
     * All rules must match
     *
     * @param AuthorizationRule ...$rules The rules
     * @return MultiRule A rule that combines all rules.
     */
    public static function all(AuthorizationRule ...$rules): MultiRule
    {
        return new MultiRule('AND', ...$rules);
    }

    /**
     * @return bool true in case of access is granted and false otherwise
     */
    public function allow(): bool
    {
        if (count($this->rules) === 0) {
            return true;
        }

        foreach ($this->rules as $rule) {
            if (!($rule instanceof AuthorizationRule)) {
                throw new RuntimeException('Invalid rule');
            }

            $result = $rule->allow();

            if ($result === true) {
                return true;
            }
        }

        return false;
    }

    /** @inheritdoc
     *
     */
    public function expression(Query $query): ?QueryExpression
    {
        $expressions = [];

        foreach ($this->rules as $rule) {
            $exp = $rule->expression($query);
            if ($exp !== null) {
                $expressions[] = $exp;
            }
        }

        if (count($expressions) === 0) {
            return null;
        }

        if (count($expressions) === 1) {
            return $expressions[0];
        }

        return $query->newExpr($expressions)->setConjunction($this->conjunction);
    }
}
