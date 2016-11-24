<div class="row">
    <div class="col-xs-12">
        <p class="text-right">
            <?= $this->Html->link(__('Add Role'), ['action' => 'add'], ['class' => 'btn btn-primary']); ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="table-responsive">
            <table class="table table-hover">
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
                            <?= $this->Html->link('', ['action' => 'view', $role->id], ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']) ?>
                            <?php if (!$role->deny_edit) : ?>
                                <?= $this->Html->link('', ['action' => 'edit', $role->id], ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                            <?php endif; ?>
                            <?php if (!$role->deny_delete) : ?>
                                <?= $this->Form->postLink('', ['action' => 'delete', $role->id], ['confirm' => __('Are you sure you want to delete # {0}?', $role->id), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->prev('< ' . __('previous')) ?>
        <?= $this->Paginator->numbers(['before' => '', 'after' => '']) ?>
        <?= $this->Paginator->next(__('next') . ' >') ?>
    </ul>
    <p><?= $this->Paginator->counter() ?></p>
</div>
