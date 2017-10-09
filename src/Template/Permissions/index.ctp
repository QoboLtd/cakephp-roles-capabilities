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
        'AdminLTE./plugins/datatables/dataTables.bootstrap',
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'AdminLTE./plugins/datatables/jquery.dataTables.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap.min'
    ],
    [
        'block' => 'scriptBottom'
    ]
);
echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable();',
    ['block' => 'scriptBottom']
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Permissions');?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                &nbsp;
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
                <thead>
                    <tr>
                        <th><?= h('Model') ?></th>
                        <th><?= h('ID'); ?></th>
                        <th><?= h('Type'); ?></th>
                        <th><?= h('Expiration Date'); ?></th>
                        <th><?= h('Status'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissions as $permission) : ?>
                    <tr>
                        <td><?= h($permission->model) ?></td>
                        <td><?= h($permission->foreign_key); ?></td>
                        <td><?= h($permission->type); ?></td>
                        <td><?= h($permission->expired); ?></td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                            <?= $this->Html->link(
                                '<i class="fa fa-eye"></i>',
                                ['plugin' => 'RolesCapabilities', 'controller' => 'Permissions', 'action' => 'view', $permission->id],
                                ['title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?php if (!$permission->deny_edit) : ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    ['plugin' => 'RolesCapabilities', 'controller' => 'Permissions', 'action' => 'edit', $permission->id],
                                    ['title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                ); ?>
                            <?php endif; ?>
                            <?php if (!$permission->deny_delete) : ?>
                                <?= $this->Form->postLink(
                                    '<i class="fa fa-trash"></i>',
                                    ['plugin' => 'RolesCapabilities', 'controller' => 'Permissions', 'action' => 'delete', $permission->id],
                                    [
                                        'confirm' => __('Are you sure you want to delete # {0}?', $permission->id),
                                        'title' => __('Delete'),
                                        'class' => 'btn btn-default btn-sm',
                                        'escape' => false
                                    ]
                                ) ?>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
