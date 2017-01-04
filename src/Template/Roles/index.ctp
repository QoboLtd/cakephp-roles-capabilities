<section class="content-header">
    <h1>Roles
        <small>
            <?= $this->Html->link(
                '<i class="fa fa-plus"></i>',
                ['plugin' => 'RolesCapabilities', 'controller' => 'Roles', 'action' => 'add'],
                ['escape' => false]
            ); ?>
        </small>
    </h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover table-condensed table-vertical-align">
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th><?= h('Groups'); ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role) : ?>
                    <tr>
                        <td>
                            <?= h($role->name) ?>
                            <p class="text-muted"><?= h($role->description) ?></p>
                        </td>
                        <td>
                            <?php
                            if (!empty($role->groups)) {
                                $groups = [];
                                foreach ($role->groups as $group) {
                                    $groups[] = $this->Html->link(h($group->name), '/groups/groups/view/' . $group->id, ['class' => 'label label-primary']);
                                }
                                sort($groups);
                                print implode(' ', $groups);
                            }
                            ?>
                        </td>
                        <td class="actions">
                            <?= $this->Html->link(
                                '<i class="fa fa-eye"></i>',
                                ['plugin' => 'RolesCapabilities', 'controller' => 'Roles', 'action' => 'view', $role->id],
                                ['title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?php if (!$role->deny_edit) : ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    ['plugin' => 'RolesCapabilities', 'controller' => 'Roles', 'action' => 'edit', $role->id],
                                    ['title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                ); ?>
                            <?php endif; ?>
                            <?php if (!$role->deny_delete) : ?>
                                <?= $this->Form->postLink(
                                    '<i class="fa fa-trash"></i>',
                                    ['plugin' => 'RolesCapabilities', 'controller' => 'Roles', 'action' => 'delete', $role->id],
                                    [
                                        'confirm' => __('Are you sure you want to delete # {0}?', $role->id),
                                        'title' => __('Delete'),
                                        'class' => 'btn btn-default btn-sm',
                                        'escape' => false
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <div class="paginator">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <?= $this->Paginator->prev('&laquo;', ['escape' => false]) ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next('&raquo;', ['escape' => false]) ?>
                </ul>
            </div>
        </div>
    </div>
</section>