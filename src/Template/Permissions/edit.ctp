<?php
echo $this->Html->css(
    [
        'AdminLTE./plugins/datepicker/datepicker3',
        'AdminLTE./plugins/select2/select2.min',
        'Groups.select2-bootstrap.min'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'AdminLTE./plugins/datepicker/bootstrap-datepicker',
        'AdminLTE./plugins/select2/select2.full.min',
    ],
    [
        'block' => 'scriptBotton'
    ]
);
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
    <h1><?= __('Edit {0}', ['Permission']) ?></h1>
</section>

