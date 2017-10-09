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

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;

/**
 *  AccessFactory Class
 *
 *  Base class for checking of user's access rights
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class AccessFactory
{
    /**
     *  Access classes suffix
     */
    const CHECKER_SUFFIX = 'Access';

    /**
     *  Access Interface name
     */
    const CHECK_ACCESS_INTERFACE = 'AccessInterface';

    /**
     *  List of rules for hasAccess() function
     *
     * @var array
     */
    protected $checkRules = [];

    /**
     *  Constructor
     *
     * @param array $rules  list of rules to override defalt ones
     */
    public function __construct(array $rules = [])
    {
        $this->checkRules = (array)Configure::read('RolesCapabilities.accessCheck.defaultRules');

        if (!empty($rules)) {
            $this->checkRules = $rules;
        }
    }

    /**
     *  hasAccess
     *
     *  Implement basic logic to check user's access
     *
     * @param array $url    URL user tries to access for
     * @param array $user   user's session data
     * @return bool         true in case of access is granted and false otherwise
     */
    public function hasAccess($url = [], $user = [])
    {
        foreach ($this->getCheckRules() as $rule) {
            $result = $this->_getCheckRuleObject($rule)->hasAccess($url, $user);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Return a list of rules to check access
     *
     * @return array    list of rules
     */
    public function getCheckRules()
    {
        return $this->checkRules;
    }

    /**
     *  Return rule object based on its name
     *
     * @param string $ruleName name of rule
     * @return object rule object or throw exception
     */
    protected function _getCheckRuleObject($ruleName)
    {
        $interface = __NAMESPACE__ . '\\' . static::CHECK_ACCESS_INTERFACE;
        $ruleClass = __NAMESPACE__ . '\\' . $ruleName . static::CHECKER_SUFFIX;

        if (class_exists($ruleClass) && in_array($interface, class_implements($ruleClass))) {
            return new $ruleClass();
        }

        throw new \InvalidArgumentException("Unknown rule [$ruleName]");
    }
}
