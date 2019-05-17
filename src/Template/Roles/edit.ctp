<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Edit {0}', ['Role']) ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <?= $this->element('role_form', ['role' => $role, 'groups' => $groups]) ?>
    <div class="box box-solid">
        <div class="box-header with-border"><i class="fa fa-unlock"></i><h3 class="box-title"><?= __('Capabilities') ?></h3></div>
        <div class="box-body"><?= $this->element('roles_capabilities', ['capabilities' => $capabilities, 'disabled' => false, 'roleCaps' => $roleCaps]) ?></div>
        <div class="box-footer"><?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary', 'id' => 'capabilities-submit']) ?></div>
    </div>
</section>
