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

use Cake\Core\App;
use Cake\Utility\Inflector;
use Qobo\Utils\Utility;

throw new \LogicException('UI to be finalized.');

$this->Html->css(['RolesCapabilities.style'], ['block' => 'css']);
$this->Html->script(['RolesCapabilities.utils'], ['block' => 'scriptBottom']);

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
?>
<div class="row">
    <div class="col-md-3 col-lg-2">
        <div id="nav-stacked">
            <!-- Main navigation -->
            <nav class="nav-capabilities" id="nav-capabilities">
                <h2 class="title">App & Plugins</h2>
                <ul>
                <?php foreach ($this->Capability->getNavigation() as $plugin => $models) : ?>
                    <li><a href="#nav-<?= md5($plugin) ?>"><?= $plugin ?> <i class="fa fa fa-angle-left pull-right"></i></a></li>
                <?php endforeach ?>
                </ul>
            </nav>
            <!-- Level 2 navigations -->
            <?php foreach ($this->Capability->getNavigation() as $plugin => $models) : ?>
            <nav class="nav-capabilities" id="nav-<?= md5($plugin) ?>">
                <h2 class="title"><?= $plugin ?></h2>
                <ul>
                    <li><a href="#nav-capabilities"><i class="fa fa-angle-double-left" aria-hidden="true"></i>Back</a></li>
                <?php foreach ($models as $model) : ?>
                    <li><a href="#<?= md5($plugin . $model) ?>" data-toggle="tab"><?= Inflector::humanize(Inflector::underscore($model)) ?></a></li>
                <?php endforeach ?>
                </ul>
            </nav>
            <?php endforeach ?>
        </div>
    </div>
    <div class="col-md-9 col-lg-10">
        <div class="tab-content">
        <?php foreach ($this->Capability->getNavigation() as $plugin => $models) : ?>
            <?php foreach ($models as $model) : ?>
            <div id="<?= md5($plugin . $model) ?>" class="tab-pane">
            <div class="nav-tabs-custom nav-tabs-capabilities">
            <ul class="nav nav-tabs">
                <li><a href="#<?= md5($plugin . $model) ?>-actions-tab" data-toggle="tab"><?= __('Actions') ?></a></li>
                <li><a href="#<?= md5($plugin . $model) ?>-resources-tab" data-toggle="tab"><?= __('Resources') ?></a></li>
            </ul>
            <div class="tab-content">
                <?php $modelName = 'App' === $plugin ? $model : $plugin . '.' . $model; ?>
                <div id="<?= md5($plugin . $model) . '-actions-tab' ?>" class="tab-pane">
                    <div class="row">
                    <?php foreach ($this->Capability->actionsOfModel($modelName) as $capability) : ?>
                        <div class="col-xs-6 col-md-4 col-lg-3">
                        <?= $this->Form->control($capability->getName(), [
                            'type' => 'checkbox',
                            'label' => $capability->getDescription(),
                            'class' => 'checkbox-capability',
                            'div' => false,
                            'disabled' => $disabled,
                            'checked' => in_array($capability->getName(), $roleCaps)
                        ]) ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
                <div id="<?= md5($plugin . $model) . '-resources-tab' ?>" class="tab-pane">
                    <div class="row">
                    <?php foreach ($this->Capability->resourcesOfModel($modelName) as $operation => $capabilities) : ?>
                        <div class="col-xs-6 col-md-4 col-lg-3">
                            <p class="h4"><?= Inflector::humanize($operation) ?></p>
                            <?php foreach ($capabilities as $capability) : ?>
                                <?= $this->Form->control($capability->getName(), [
                                    'type' => 'checkbox',
                                    'label' => $capability->getDescription(),
                                    'class' => 'checkbox-capability',
                                    'div' => false,
                                    'escape' => false,
                                    'data-enforced' => htmlspecialchars(json_encode($capability->getEnforcedNames()), ENT_QUOTES, 'UTF-8'),
                                    'data-overridden-by' => htmlspecialchars(json_encode($capability->getOverriddenByNames()), ENT_QUOTES, 'UTF-8'),
                                    'disabled' => $disabled,
                                    'checked' => in_array($capability->getName(), $roleCaps)
                                ]) ?>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>
                    </div>
                </div>
            </div>
            </div>
            </div>
            <?php endforeach ?>
        <?php endforeach ?>
        </div>
    </div>
</div>
