<?php

namespace RolesCapabilities\CheckAccess;

use Cake\Network\Exception\ForbiddenException;

/**
 *  CheckAccessFactory Class
 *
 *  Base class for checking of user's access rights
 *
 * @author Michael Stepanov <m.stepanov@qobo.biz>
 */
class CheckAccessFactory
{
    /**
     *  Check Access classes suffix
     *
     */
    const CHECKER_SUFFIX = 'CheckAccess';

    /**
     *  Check Access Interface name  
     *
     */
    const CHECK_ACCESS_INTERFACE = 'CheckAccessInterface'; 
    
    /**
     *  List of rules for checkAccess() function  
     *
     * @var array
     */
    protected $_checkRules = [
        'Authorize', 'SuperUser', 'Capabilities'
    ];
   
    
    /**
     *  checkAccess
     *
     *  Implement basic logic to check user's access
     *
     * @param array $url    URL user tries to access for
     * @param array $user   user's session data
     * @return void
     */
    public function checkAccess($url=[], $user=[])
    {
        foreach ($this->_checkRules as $rule) {
            $result = $this->_getCheckRuleObject($rule)->checkAccess($url, $user);

            if ($result) {
                return;
            }
        }
        
        throw new ForbiddenException();
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
