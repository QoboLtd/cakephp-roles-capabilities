<?php
echo $this->Html->css(
    [
        'AdminLTE./plugins/select2/select2.min',
        'RolesCapabilities.select2-bootstrap.min'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script('AdminLTE./plugins/select2/select2.full.min', ['block' => 'scriptBotton']);
echo $this->Html->scriptBlock(
    '$(".select2").select2({
        theme: "bootstrap",
        placeholder: "Select an option",
        allowClear: true
    });',
    ['block' => 'scriptBotton']
);
?>
<section class="content-header">
    <h1><?= __('Create {0}', ['Role']) ?></h1>
</section>
<section class="content">
    <?= $this->Form->create($role) ?>
    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $this->Form->input('name'); ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->input('description'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $this->Form->label(__('Groups')); ?>
                    <?= $this->Form->select('groups._ids', $groups, [
                        'class' => 'select2',
                        'multiple' => true
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Capabilities') ?></h3>
        </div>
        <div class="box-body">
            <div class="row">
            <?php ksort($capabilities); foreach ($capabilities as $groupName => $groupCaps) : ?>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="form-group text">
                        <label><?= $this->cell('RolesCapabilities.Capability::groupName', [$groupName]) ?></label>
                        <?php
                        asort($groupCaps);
                        foreach ($groupCaps as $k => $v) {
                            echo $this->Form->input('capabilities[_names][' . $k . ']', [
                                'type' => 'checkbox',
                                'label' => $v,
                                'div' => false
                            ]);
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <div class="box-footer">
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>