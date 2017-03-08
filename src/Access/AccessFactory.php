<?php

namespace RolesCapabilities\Access;

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
     *
     */
    const CHECKER_SUFFIX = 'Access';

    /**
     *  Access Interface name
     *
     */
    const CHECK_ACCESS_INTERFACE = 'AccessInterface';

    /**
     *  List of rules for hasAccess() function
     *
     * @var array
     */
    protected $_checkRules = [
        'SuperUser', 'Capabilities'
    ];

    /**
     *  Constructor
     *
     *
     */
    public function __construct(array $rules = [])
    {
        if (!empty($rules)) {
            $this->_checkRules = $rules;
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
        return $this->_checkRules;
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
    }
}
