<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Personal Permission'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="personalPermissions index large-9 medium-8 columns content">
    <h3><?= __('Personal Permissions') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('foreign_key') ?></th>
                <th scope="col"><?= $this->Paginator->sort('model') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('creator') ?></th>
                <th scope="col"><?= $this->Paginator->sort('type') ?></th>
                <th scope="col"><?= $this->Paginator->sort('is_active') ?></th>
                <th scope="col"><?= $this->Paginator->sort('expired') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($personalPermissions as $personalPermission): ?>
            <tr>
                <td><?= h($personalPermission->id) ?></td>
                <td><?= h($personalPermission->foreign_key) ?></td>
                <td><?= h($personalPermission->model) ?></td>
                <td><?= $personalPermission->has('user') ? $this->Html->link($personalPermission->user->id, ['controller' => 'Users', 'action' => 'view', $personalPermission->user->id]) : '' ?></td>
                <td><?= h($personalPermission->creator) ?></td>
                <td><?= h($personalPermission->type) ?></td>
                <td><?= h($personalPermission->is_active) ?></td>
                <td><?= h($personalPermission->expired) ?></td>
                <td><?= h($personalPermission->created) ?></td>
                <td><?= h($personalPermission->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $personalPermission->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $personalPermission->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $personalPermission->id], ['confirm' => __('Are you sure you want to delete # {0}?', $personalPermission->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
