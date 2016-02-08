<?php
namespace RolesCapabilities\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class CapabilityComponent extends Component
{
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
