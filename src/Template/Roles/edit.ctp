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

echo $this->Html->css(
    [
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'AdminLTE./plugins/select2/select2.full.min',
        'Qobo/Utils.select2.init',
        'RolesCapabilities.utils'
    ],
    [
        'block' => 'scriptBottom'
    ]
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Edit {0}', ['Role']) ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <?= $this->Form->create($role, ['id' => 'capabilities-form']) ?>
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $this->Form->input('name'); ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->input('description'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $this->Form->label(__('Groups')); ?>
                    <?= $this->Form->select('groups._ids', $groups, [
                        'class' => 'select2',
                        'multiple' => true
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <?= $this->Form->hidden('capabilities', ['id' => 'capabilities-input']) ?>
    <?= $this->Form->end() ?>
    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Capabilities') ?></h3>
        </div>
        <?php
            $tabs = '';
            $count = 0;
        ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                <div style="height: 500px; overflow-x:hidden; overflow-y:scroll;">
                <ul class="nav nav-pills nav-stacked">
                <?php ksort($capabilities); foreach ($capabilities as $groupName => $groupCaps) : ?>
                    <?php
                    if (empty($groupCaps)) {
                        continue;
                    }
                        $active = ++$count == 1 ? 'active' : '';
                        $tabId = Inflector::underscore(preg_replace('/\\\/', '', $groupName));
                        $tabs .= '<div id="' . $tabId . '" class="tab-pane ' . $active . '">';

                        $subtabs = '';
                        $subtabs_menu = '<ul class="nav nav-tabs">';

                        $sCount = 0;
                    foreach ($groupCaps as $type => $caps) {
                        usort($caps, function ($a, $b) {
                            return strcmp($a->getDescription(), $b->getDescription());
                        });

                        $title = Inflector::humanize($type) . ' ' . __('Access');
                        $slug = $tabId . '_' . $type . '_' . 'access';

                        $sActive = ++$sCount == 1 ? 'active' : '';
                        $subtabs_menu .= '<li class="' . $sActive . '"><a href="#' . $slug . '" data-toggle="tab">' . $title . '</a>';
                        $subtabs .= '<div id="' . $slug . '" class="tab-pane ' . $sActive . '">';

                        foreach ($caps as $cap) {
                            $subtabs .= $this->Form->input($cap->getName(), [
                                'type' => 'checkbox',
                                'label' => $cap->getDescription(),
                                'class' => 'checkbox-capability',
                                'div' => false,
                                'checked' => in_array($cap->getName(), $roleCaps)
                            ]);
                        }
                        $subtabs .= '</div>';
                    }
                        $subtabs_menu .= '</ul>';
                        $tabs .= $subtabs_menu . '<div class="tab-content clearfix">' . $subtabs . "</div>";
                        $tabs .= '</div>';
                    ?>
                    <li class="<?= $active ?>"><a href="#<?= $tabId ?>" data-toggle="tab"><?= $this->cell('RolesCapabilities.Capability::groupName', [$groupName]) ?></a></li>
                <?php endforeach; ?>
                </ul>
                </div>
                </div>
                <div class="col-md-10">
                    <div class="tab-content clearfix">
                        <?= $tabs ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary', 'id' => 'capabilities-submit']) ?>
        </div>
</section>
