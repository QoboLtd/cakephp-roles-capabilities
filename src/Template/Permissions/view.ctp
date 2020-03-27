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
?>
<section class="content-header">
    <h1><?= $this->Html->link(
        __d('Qobo/RolesCapabilities', ' Permission'),
        ['plugin' => 'RolesCapabilities', 'controller' => 'Permissions', 'action' => 'index']
    ) . ' &raquo; ' . h($permission->model) ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <i class="fa fa-shield"></i>

                    <h3 class="box-title">Details</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt><?= __d('Qobo/RolesCapabilities', 'Model') ?></dt>
                        <dd><?= h($permission->model) ?></dd>
                        <dt><?= __d('Qobo/RolesCapabilities', 'Type') ?></dt>
                        <dd><?= h($permission->type) ?></dd>
                        <dt><?= __d('Qobo/RolesCapabilities', 'Entity ID') ?></dt>
                        <dd><?= h($permission->owner_foreign_key) ?></dd>
                        <dt><?= __d('Qobo/RolesCapabilities', 'Entity Model') ?></dt>
                        <dd><?= h($permission->owner_model) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</section>
