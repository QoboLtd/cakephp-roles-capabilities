<?php
echo $this->Html->css(
    [
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'AdminLTE./plugins/select2/select2.full.min',
        'Qobo/Utils.select2.init',
        'RolesCapabilities.utils'
    ],
    [
        'block' => 'scriptBottom'
    ]
);

