<?php
/**
 * @var \App\View\AppView $this
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Personal Permission'), ['action' => 'edit', $personalPermission->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Personal Permission'), ['action' => 'delete', $personalPermission->id], ['confirm' => __('Are you sure you want to delete # {0}?', $personalPermission->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Personal Permissions'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Personal Permission'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="personalPermissions view large-9 medium-8 columns content">
    <h3><?= h($personalPermission->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($personalPermission->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Foreign Key') ?></th>
            <td><?= h($personalPermission->foreign_key) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Model') ?></th>
            <td><?= h($personalPermission->model) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $personalPermission->has('user') ? $this->Html->link($personalPermission->user->id, ['controller' => 'Users', 'action' => 'view', $personalPermission->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Creator') ?></th>
            <td><?= h($personalPermission->creator) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Type') ?></th>
            <td><?= h($personalPermission->type) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Expired') ?></th>
            <td><?= h($personalPermission->expired) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($personalPermission->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($personalPermission->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Is Active') ?></th>
            <td><?= $personalPermission->is_active ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
</div>
