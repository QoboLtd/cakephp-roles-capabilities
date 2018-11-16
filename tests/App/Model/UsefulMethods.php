<?php
namespace RolesCapabilities\Test\App\Model;

use RolesCapabilities\FilterQuery;

/**
 * Class for useful methods in regards testing
 */
class UsefulMethods
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Return method back
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public static function callPrivateMethod(&$object, $methodName, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param string &$object    Return method back
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public static function callStaticPrivateMethod(String $object, String $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);

        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($reflection, $parameters);
    }
}
