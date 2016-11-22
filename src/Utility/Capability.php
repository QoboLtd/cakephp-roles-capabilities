<?php
namespace RolesCapabilities\Utility;

use Cake\Core\App;
use Cake\Core\Plugin;
use DirectoryIterator;

class Capability
{
    /**
     * Method that retrieves all capabilities.
     *
     * @return array capabilities
     */
    public function getAllCapabilities()
    {
        $result = [];
        foreach (self::getControllers() as $controller) {
            if (is_callable([$controller, 'getCapabilities'])) {
                foreach ($controller::getCapabilities($controller) as $type => $capabilities) {
                    foreach ($capabilities as $capability) {
                        $result[$controller][$capability->getName()] = $capability->getDescription();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Method that returns all controller names.
     * @param  bool  $includePlugins flag for including plugin controllers
     * @return array                 controller names
     */
    public static function getControllers($includePlugins = true)
    {
        $controllers = self::getDirControllers(APP . 'Controller' . DS);

        if (true === $includePlugins) {
            $plugins = Plugin::loaded();
            foreach ($plugins as $plugin) {
                // plugin path
                $path = Plugin::path($plugin) . 'src' . DS . 'Controller' . DS;
                $controllers = array_merge($controllers, self::getDirControllers($path, $plugin));
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
    public static function getDirControllers($path, $plugin = null, $fqcn = true)
    {
        $controllers = [];
        if (file_exists($path)) {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $fileinfo) {
                $className = $fileinfo->getBasename('.php');
                if ($fileinfo->isFile() && 'AppController' !== $className) {
                    if (!empty($plugin)) {
                        $className = $plugin . '.' . $className;
                    }

                    if (true === $fqcn) {
                        $className = App::className($className, 'Controller');
                    }

                    $controllers[] = $className;
                }
            }
        }

        return $controllers;
    }
}
