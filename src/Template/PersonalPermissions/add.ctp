<?php
echo $this->Html->css(
    [
        'AdminLTE./plugins/select2/select2.min',
        'Groups.select2-bootstrap.min'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script('AdminLTE./plugins/select2/select2.full.min', ['block' => 'scriptBotton']);
echo $this->Html->scriptBlock(
    '$(".select2").select2({
        theme: "bootstrap",
        tags: "true",
        placeholder: "Select an option",
        allowClear: true
    });',
    ['block' => 'scriptBotton']
);
?>
<section class="content-header">
    <h1><?= __('Add {0}', ['Personal Permissions']) ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="box box-solid">
                <?= $this->Form->create($personalPermission) ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->Form->input('user_id'); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->Form->input('type'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= $this->Form->input('expired', ['type' => 'text']); ?>    
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= $this->Form->input('is_active'); ?> 
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    &nbsp;
                    <?= $this->Form->button(__('Cancel'), ['class' => 'btn remove-client-validation', 'name' => 'btn_operation', 'value' => 'cancel']); ?>
                </div>
                <?= $this->Form->end() ?>
        </div>
    </div>
</section>
