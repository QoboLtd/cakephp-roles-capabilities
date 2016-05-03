<div class="row">
    <div class="col-xs-12">
        <?= $this->Form->create($role) ?>
        <fieldset>
            <legend><?= __('Add Role') ?></legend>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">&nbsp;</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('name'); ?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->input('groups._ids', ['options' => $groups]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Capabilities') ?></h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?php
                        ksort($capabilities);
                        foreach ($capabilities as $group_name => $group_caps) :
                    ?>
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="form-group text">
                                <label><?= $this->cell('RolesCapabilities.Capability::groupName', [$group_name]) ?></label>
                            <?php
                                asort($group_caps);
                                foreach ($group_caps as $k => $v) {
                                    echo $this->Form->input('capabilities[_names][' . $k .']', [
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
            </div>
        </fieldset>
        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
