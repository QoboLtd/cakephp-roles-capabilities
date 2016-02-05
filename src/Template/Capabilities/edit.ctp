<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $capability->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $capability->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Capabilities'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Roles'), ['controller' => 'Roles', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Role'), ['controller' => 'Roles', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="capabilities form large-9 medium-8 columns content">
    <?= $this->Form->create($capability) ?>
    <fieldset>
        <legend><?= __('Edit Capability') ?></legend>
        <?php
            echo $this->Form->input('name');
            echo $this->Form->input('roles._ids', ['options' => $roles]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
