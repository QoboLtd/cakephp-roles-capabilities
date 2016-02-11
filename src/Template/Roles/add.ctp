<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Roles'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="roles form large-9 medium-8 columns content">
    <?= $this->Form->create($role) ?>
    <fieldset>
        <legend><?= __('Add Role') ?></legend>
        <?php
            echo $this->Form->input('name');
        ?>
        <div class="form-group text">
            <label for="capabilities-names">Capabilities</label>
            <?php
                foreach ($capabilities as $k => $v) {
                    echo $this->Form->input('capabilities[_names][' . $k .']', [
                        'type' => 'checkbox',
                        'label' => $v
                    ]);
                }
            ?>
        </div>
        <?php
            echo $this->Form->input('groups._ids', ['options' => $groups]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
