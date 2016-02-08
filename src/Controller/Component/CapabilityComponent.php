<?php
namespace RolesCapabilities\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class CapabilityComponent extends Component
{
    /**
     * Allow flag
     */
    const ALLOW = true;

    /**
     * Deny flag
     */
    const DENY = false;

    public $components = ['Auth'];

    /**
     * Current controller
     * @var object
     */
    protected $_controller;

    /**
     * Initialize method
     * @param  array  $config configuration array
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_controller = $this->_registry->getController();
    }

    /**
     * Method that retrieves controller capabilities
     * @return array controller capabilities
     */
    protected function _getControllerCapabilities()
    {
        $caps = [];
        if (method_exists($this->_controller, 'getCapabilities')) {
            $caps = array_keys($this->_controller->getCapabilities());
        }

        return $caps;
    }

    /**
     * Method that retrieves all defined capabilities
     * @return array capabilities
     */
    public function getAllCapabilities()
    {
        $capabilities = [];
        // get all controllers
        $controllers = $this->_getAllControllers();

        foreach ($controllers as $controller) {
            $classObj = new $controller;
            if (method_exists($classObj, 'getCapabilities')) {
                $controllerCaps = array_keys($classObj->getCapabilities());
                $capabilities = array_merge($capabilities, $controllerCaps);
            }
        }


        return $capabilities;
    }

    /**
     * Method that returns all controller names.
     * @param  bool  $includePlugins flag for including plugin controllers
     * @return array                 controller names
     */
    protected function _getAllControllers($includePlugins = true)
    {
        $controllers = $this->_getDirControllers(APP . 'Controller' . DS);

        if (true === $includePlugins) {
            $plugins = \Cake\Core\Plugin::loaded();
            foreach ($plugins as $plugin) {
                // plugin path
                $path = \Cake\Core\Plugin::path($plugin) . 'src' . DS . 'Controller' . DS;
                $controllers = array_merge($controllers, $this->_getDirControllers($path, $plugin));
            }
        }

        return $controllers;
    }

    /**
     * Method that retrieves controller names
     * found on the provided directory path.
     * @param  string $path   directory path
     * @param  string $plugin plugin name
     * @param  bool   $fqcn   flag for using fqcn
     * @return array          controller names
     */
    protected function _getDirControllers($path, $plugin = null, $fqcn = true)
    {
        $controllers = [];
        if (file_exists($path)) {
            $dir = new \DirectoryIterator($path);
            foreach ($dir as $fileinfo) {
                $className = $fileinfo->getBasename('.php');
                if ($fileinfo->isFile() && 'AppController' !== $className) {
                    if (!empty($plugin)) {
                        $className = $plugin . '.' . $className;
                    }

                    if (true === $fqcn) {
                        $className = \Cake\Core\App::className($className, 'Controller');
                    }

                    $controllers[] = $className;
                }
            }
        }

        return $controllers;
    }
}
