<?= $this->Form->create($role, ['id' => 'capabilities-form']) ?>
<div class="row">
    <div class="col-md-6">
        <?= $this->Form->control('name'); ?>
    </div>
    <div class="col-md-6">
        <?= $this->Form->control('description'); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?= $this->Form->label(__d('Qobo/RolesCapabilities', 'Groups')); ?>
        <?= $this->Form->select('groups._ids', $groups, [
            'class' => 'select2',
            'multiple' => true
        ]); ?>
    </div>
</div>
<?= $this->Form->hidden('capabilities', ['id' => 'capabilities-input']) ?>
<?= $this->Form->end() ?>
