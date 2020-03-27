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

echo $this->Html->css('RolesCapabilities.style', ['block' => 'css']);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?php echo $this->Html->link(__d('Qobo/RolesCapabilities', 'Roles'), [
                'plugin' => 'RolesCapabilities',
                'controller' => 'Roles',
                'action' => 'index'
            ]) . ' &raquo; ' . h($role->get('name')) ?>
            </h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border"><i class="fa fa-lock"></i><h3 class="box-title">Details</h3></div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt><?= __d('Qobo/RolesCapabilities', 'Name') ?></dt>
                        <dd><?= h($role->get('name')) ?></dd>
                        <dt><?= __d('Qobo/RolesCapabilities', 'Description') ?></dt>
                        <dd><?= h($role->get('description')) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border"><i class="fa fa-users"></i><h3 class="box-title"><?= __d('Qobo/RolesCapabilities', 'Groups'); ?></h3></div>
                <div class="box-body">
                <?php if (! empty($role->get('groups'))) : ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-condensed table-vertical-align">
                            <thead>
                                <tr>
                                    <th><?= __d('Qobo/RolesCapabilities', 'Name') ?></th>
                                    <th><?= __d('Qobo/RolesCapabilities', 'Description') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($role->get('groups') as $group) : ?>
                                <tr>
                                    <td><?= $this->Html->link($group->get('name'), [
                                        'plugin' => 'Groups',
                                        'controller' => 'Groups',
                                        'action' => 'view',
                                        $group->get('id')
                                    ]) ?></td>
                                    <td><?= h($group->get('description')) ?></td>
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
    <hr />
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-solid">
                <div class="box-header with-border"><i class="fa fa-unlock"></i><h3 class="box-title"> <?= __d('Qobo/RolesCapabilities', 'Capabilities'); ?></h3></div>
                <div class="box-body"><?= $this->element('roles_capabilities', ['capabilities' => $capabilities, 'disabled' => true, 'roleCaps' => $roleCaps]) ?></div>
            </div>
        </div>
    </div>
</section>
