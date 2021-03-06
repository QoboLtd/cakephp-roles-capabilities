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

echo $this->element('roles_scripts_css');
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __d('Qobo/RolesCapabilities', 'Create {0}', ['Role']) ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <?= $this->element('role_form', ['role' => $role, 'groups' => $groups]) ?>
        </div>
    </div>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __d('Qobo/RolesCapabilities', 'Capabilities') ?></h3>
        </div>
        <div class="box-body">
            <?= $this->element('roles_capabilities', ['capabilities' => $capabilities, 'disabled' => false, 'roleCaps' => []]) ?>
        </div>
        <div class="box-footer">
            <?= $this->Form->button(__d('Qobo/RolesCapabilities', 'Submit'), ['class' => 'btn btn-primary', 'id' => 'capabilities-submit']) ?>
        </div>
    </div>
</section>
