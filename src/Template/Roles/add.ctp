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
            <h4><?= __('Create {0}', ['Role']) ?></h4>
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
            <div class="box-tools pull-right">
                <?= $this->Form->input('collapse_all', [
                    'id' => 'collapse_all',
                    'type' => 'checkbox',
                    'div' => false,
                    'label' => __('Expand/Collapse All')
                ]); ?>
            </div>
        </div>
        <?php
            $count = 0;
            $maxNum = 3;
        ?>
        <div class="box-body">
            <div class="row">
            <?php ksort($capabilities); foreach ($capabilities as $groupName => $groupCaps) : ?>
                <?php
                if (empty($groupCaps)) {
                    continue;
                }
                ?>
                <?php if ($count > $maxNum) : ?>
                    </div>
                    <div class="row">
                    <?php $count = 0; ?>
                <?php endif; ?>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="box box-default box-solid permission-box collapsed-box">
                        <div class="box-header">
                            <h3 class="box-title"><?= $this->cell('RolesCapabilities.Capability::groupName', [$groupName]) ?></h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php
                            $selectAllName = 'cap__' . preg_replace('/\\\/', '_', $groupName);
                            echo $this->Form->input($selectAllName, [
                                'id' => $selectAllName,
                                'type' => 'checkbox',
                                'class' => 'select_all',
                                'div' => false,
                                'label' => __('Select All'),
                            ]);
                            echo $this->Html->tag('hr');

                            foreach ($groupCaps as $type => $caps) {
                                usort($caps, function ($a, $b) {
                                    return strcmp($a->getDescription(), $b->getDescription());
                                });
                                echo $this->Html->tag('h4', Inflector::humanize($type) . ' ' . __('Access'));
                                foreach ($caps as $cap) {
                                    echo $this->Form->input($cap->getName(), [
                                        'type' => 'checkbox',
                                        'label' => $cap->getDescription(),
                                        'class' => 'checkbox-capability',
                                        'div' => false
                                    ]);
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php $count++; ?>
            <?php endforeach; ?>
            </div>
        </div>
        <div class="box-footer">
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary', 'id' => 'capabilities-submit']) ?>
        </div>
    </div>
</section>
