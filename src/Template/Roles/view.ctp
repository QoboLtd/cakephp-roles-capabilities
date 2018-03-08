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

echo $this->Html->css(
    [
        'RolesCapabilities.style'
    ],
    [
        'block' => 'css'
    ]
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?php echo $this->Html->link(__('Roles'), [
                'plugin' => 'RolesCapabilities',
                'controller' => 'Roles',
                'action' => 'index'
            ]) . ' &raquo; ' . h($role->name) ?>
            </h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <i class="fa fa-unlock"></i>

                    <h3 class="box-title">Details</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt><?= __('Name') ?></dt>
                        <dd><?= h($role->name) ?></dd>
                        <dt><?= __('Description') ?></dt>
                        <dd><?= h($role->description) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#capabilities" aria-controls="capabilities" role="tab" data-toggle="tab">
                            <?= __('Capabilities'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#groups" aria-controls="groups" role="tab" data-toggle="tab">
                            <?= __('Groups'); ?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="capabilities">
                        <?= $this->element('roles_capabilities', ['capabilities' => $capabilities, 'disabled' => true, 'roleCaps' => $roleCaps]) ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="groups">
                        <?php if (!empty($role->groups)) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-condensed table-vertical-align">
                                <thead>
                                    <tr>
                                        <th><?= __('Name') ?></th>
                                        <th><?= __('Description') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($role->groups as $groups) : ?>
                                    <tr>
                                        <td><?= $this->Html->link($groups->name, [
                                            'plugin' => 'Groups',
                                            'controller' => 'Groups',
                                            'action' => 'view',
                                            $groups->id
                                        ]) ?></td>
                                        <td><?= h($groups->description) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
