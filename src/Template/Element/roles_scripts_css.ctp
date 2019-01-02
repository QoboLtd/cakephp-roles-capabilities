<?php
echo $this->Html->css(
    [
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
        'RolesCapabilities.style'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'AdminLTE./bower_components/select2/dist/js/select2.full.min',
        'Qobo/Utils.select2.init',
        'RolesCapabilities.utils'
    ],
    [
        'block' => 'scriptBottom'
    ]
);
