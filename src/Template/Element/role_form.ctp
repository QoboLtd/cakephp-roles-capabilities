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

echo $this->Form->create($role, ['id' => 'capabilities-form']);
?>
<div class="row">
    <div class="col-md-6">
        <div class="box box-solid">
            <div class="box-header with-border"><i class="fa fa-lock"></i><h3 class="box-title">Details</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6"><?= $this->Form->control('name'); ?></div>
                    <div class="col-md-6"><?= $this->Form->control('description'); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-solid">
            <div class="box-header with-border"><i class="fa fa-users"></i><h3 class="box-title">Groups</h3></div>
            <div class="box-body">
                <?= $this->Form->label(__d('Qobo/RolesCapabilities', 'Groups')); ?>
                <?= $this->Form->select('groups._ids', $groups, ['class' => 'select2', 'multiple' => true]); ?>
            </div>
        </div>
    </div>
</div>
<?= $this->Form->hidden('capabilities', ['id' => 'capabilities-input']) ?>
<?= $this->Form->end() ?>
