<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $role->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $role->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Roles'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="roles form large-9 medium-8 columns content">
    <?= $this->Form->create($role) ?>
    <fieldset>
        <legend><?= __('Edit Role') ?></legend>
        <?php
            echo $this->Form->input('name');
            echo $this->Form->input('groups._ids', ['options' => $groups]);
        ?>
    </fieldset>
    <fieldset>
        <div class="form-group text">
            <legend><?= __('Capabilities') ?></legend>
            <?php
                foreach ($capabilities as $group_name => $group_caps) {
                    echo '<label>' . $group_name . '</label>';
                    foreach ($group_caps as $k => $v) {
                        echo $this->Form->input('capabilities[_names][' . $k .']', [
                            'type' => 'checkbox',
                            'label' => $v,
                            'div' => false,
                            'checked' => in_array($k, $roleCaps)
                        ]);
                    }
                }
            ?>
        </div>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
