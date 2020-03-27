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

use Cake\Utility\Inflector;

if (true !== $disabled) {
    echo $this->Html->css([
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
        'RolesCapabilities.style'
    ], ['block' => 'css']);
    echo $this->Html->script([
        'AdminLTE./bower_components/select2/dist/js/select2.full.min',
        'Qobo/Utils.select2.init',
        'RolesCapabilities.utils'
    ], ['block' => 'scriptBottom']);
}

$count = 0;
ksort($capabilities);

$getGroupName = function ($name) {
    $parts = array_map(function ($n) {
        return Inflector::humanize(Inflector::underscore(str_replace('Controller', '', $n)));
    }, explode('\\', $name));

    $parts = array_filter($parts);
    // get just the controller and plugin names
    $parts = array_slice($parts, -2);

    return implode(' :: ', $parts);
};

?>
<div class="row">
    <div class="col-md-2">
        <div class="fixed-height-box">
            <ul class="nav nav-pills nav-stacked">
            <?php foreach ($capabilities as $groupName => $groupCaps) :
                if (empty($groupCaps)) {
                    continue;
                }

                $active = ++$count == 1 ? 'active' : '';
                $tabId = Inflector::underscore(preg_replace('/\\\/', '', $groupName));
                ?>
                <li class="<?= $active ?>"><a href="#<?= $tabId ?>" data-toggle="tab"><?= $getGroupName($groupName) ?></a></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-10">
        <div class="tab-content clearfix">
        <?php $count = 0; ?>
        <?php foreach ($capabilities as $groupName => $groupCaps) : ?>
            <?php
            if (empty($groupCaps)) {
                continue;
            }
                $active = ++$count == 1 ? 'active' : '';
                $tabId = Inflector::underscore(preg_replace('/\\\/', '', $groupName));
            ?>
            <div id="<?= $tabId ?>" class="tab-pane <?= $active ?>">
            <ul class="nav nav-tabs">
            <?php
            $sCount = 0;
            foreach ($groupCaps as $type => $caps) {
                usort($caps, function ($a, $b) {
                    return strcmp($a->getDescription(), $b->getDescription());
                });

                $title = Inflector::humanize($type) . ' ' . __d('Qobo/RolesCapabilities', 'Access');
                $type = preg_replace('/(\(|\))/', '', $type);
                $slug = $tabId . '_' . $type . '_' . 'access';

                $sActive = ++$sCount == 1 ? 'active' : '';
                echo '<li class="' . $sActive . '"><a href="#' . $slug . '" data-toggle="tab">' . $title . '</a>';
            }
            ?>
            </ul>
            <div class="tab-content clearfix">
            <?php
            $sCount = 0;
            foreach ($groupCaps as $type => $caps) {
                usort($caps, function ($a, $b) {
                    return strcmp($a->getDescription(), $b->getDescription());
                });

                $title = Inflector::humanize($type) . ' ' . __d('Qobo/RolesCapabilities', 'Access');
                $type = preg_replace('/(\(|\))/', '', $type);
                $slug = $tabId . '_' . $type . '_' . 'access';

                $sActive = ++$sCount == 1 ? 'active' : '';
                echo '<div id="' . $slug . '" class="tab-pane ' . $sActive . '">';

                echo $this->Form->control($slug, [
                    'type' => 'checkbox',
                    'label' => 'Select All',
                    'class' => 'select_all',
                    'div' => false,
                    'disabled' => $disabled,
                ]);
                echo '<hr/>';

                foreach ($caps as $cap) {
                    echo $this->Form->control($cap->getName(), [
                        'type' => 'checkbox',
                        'label' => $cap->getDescription(),
                        'class' => 'checkbox-capability',
                        'div' => false,
                        'disabled' => $disabled,
                        'checked' => in_array($cap->getName(), $roleCaps)
                    ]);
                }
                echo '</div>';
            }
            ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
