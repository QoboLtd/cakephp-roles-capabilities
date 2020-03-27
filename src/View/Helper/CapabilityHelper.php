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
namespace RolesCapabilities\View\Helper;

use Cake\Core\App;
use Cake\View\Helper;
use Qobo\Utils\Utility;
use RolesCapabilities\Capability\Generator;

final class CapabilityHelper extends Helper
{
    /**
     * Navigation links getter.
     *
     * @return mixed[]
     */
    public function getNavigation(): array
    {
        return self::groupModelsByPlugin(self::getModels());
    }

    public function resourcesOfModel(string $modelName): array
    {
        return (new Generator())->resources($modelName);
    }

    public function actionsOfModel(string $modelName): array
    {
        return (new Generator())->actions($modelName);
    }

    /**
     * Model names getter.
     *
     * @return string[]
     */
    private static function getModels(): array
    {
        $result = array_map(function($controller) {
            return App::shortName($controller, 'Controller', 'Controller');
        }, Utility::getControllers());

        sort($result);

        return $result;
    }

    /**
     * Model names grouped-by plugin getter.
     *
     * @param string[] $models Model names
     * @return mixed[]
     */
    private static function groupModelsByPlugin(array $models) : array
    {
        $result = [];

        foreach ($models as $model) {
            list($plugin, $table) = pluginSplit($model);
            $plugin = empty($plugin) ? 'App' : $plugin;

            $result[$plugin][] = $table;
        }

        return $result;
    }
}
