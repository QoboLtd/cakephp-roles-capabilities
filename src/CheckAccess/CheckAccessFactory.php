<?php

namespace RolesCapabilities\CheckAccess;

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
    protected $_listOfRules = [
        'Authorize', 'SuperUser',
    ];

    /**
     *  Request details
     *
     * @var array
     */
    protected $_url = [];

    /**
     *  User's session details
     *
     * @var array
     */
    protected $_user = []; 
    
    /**
     *  Class constructor
     *
     * @param array $url    request details
     * @param array $user   user's session
     */
    public function __construct($url, $user)
    {
        $this->_url = $url;
        $this->_user = $user;
    }

    /**
     *  checkAccess
     *
     *  Implement basic logic to check user's access
     *
     * @param array $url    URL user tries to access for
     * @param array $user   user's session data
     * @return void
     */
    public function checkAccess()
    {
        $result = false;

        foreach ($this->_listOfRules as $rule) {
            $this->_getCheckRule($rule)->checkAccess($this->_url, $this->_user);
        }

        return $result;
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
    }
}
