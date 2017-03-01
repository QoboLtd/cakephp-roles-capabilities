<?php

namespace RolesCapabilities\CheckAccess;

use Cake\Core\App;
use Cake\ORM\TableRegistry;
use ReflectionClass;
use ReflectionMethod;
use RolesCapabilities\Capability as Cap;
use Cake\Utility\Inflector;
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
    protected $_listOfRules = [
        'Authorize', 'SuperUser',
    ];
   
    /**
     * Capabilities Table instance.
     *
     * @var object
     */
    protected static $_capabilitiesTable;

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
        if (empty($user)) {
            return false;
        }
        
        if (!empty($user['is_superuser']) && $user['is_superuser']) {
            return true;
        }
    
        $controllerName = static::_getCapabilitiesTable()->getControllerFullName($url);

        $actionCapabilities = [];
        if (!empty($url['action'])) {
            $actionCapabilities = static::_getCapabilitiesTable()->getCapabilities($controllerName, [$url['action']]);
        }
        
        // if action capabilities is empty, means that current controller or action are skipped
        if (empty($actionCapabilities)) {
            return false;
        }

        $hasAccess = static::_getCapabilitiesTable()->hasTypeAccess(static::_getCapabilitiesTable()->getTypeFull(), $actionCapabilities, $user, $url);

        // if user has no full access capabilities
        if (!$hasAccess) {
            $hasAccess = static::_getCapabilitiesTable()->hasTypeAccess(static::_getCapabilitiesTable()->getTypeOwner(), $actionCapabilities, $user, $url);
        }

        if (!$hasAccess) {
            throw new ForbiddenException();
        }

        /*
        $result = false;

        foreach ($this->_listOfRules as $rule) {
            $this->_getCheckRuleObject($rule)->checkAccess($url, $user);
        }

        return $result;
        */
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

    /**
     * Get instance of Capabilities Table.
     *
     * @return object Capabilities Table object
     */
    protected static function _getCapabilitiesTable()
    {
        if (empty(static::$_capabilitiesTable)) {
            static::$_capabilitiesTable = TableRegistry::get('RolesCapabilities.Capabilities');
        }

        return static::$_capabilitiesTable;
    }
}
