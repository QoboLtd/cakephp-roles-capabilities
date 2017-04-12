<?php
/**
 * @var \App\View\AppView $this
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
            __('Delete'),
            ['action' => 'delete', $personalPermission->id],
            ['confirm' => __('Are you sure you want to delete # {0}?', $personalPermission->id)]
        )
        ?></li>
        <li><?= $this->Html->link(__('List Personal Permissions'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="personalPermissions form large-9 medium-8 columns content">
    <?= $this->Form->create($personalPermission) ?>
    <fieldset>
        <legend><?= __('Edit Personal Permission') ?></legend>
        <?php
            echo $this->Form->control('foreign_key');
            echo $this->Form->control('model');
            echo $this->Form->control('user_id', ['options' => $users]);
            echo $this->Form->control('creator');
            echo $this->Form->control('type');
            echo $this->Form->control('is_active');
            echo $this->Form->control('expired', ['empty' => true]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
