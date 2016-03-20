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
                        <div class="col-xs-6">
                            <?= $this->Form->input('name'); ?>
                        </div>
                        <div class="col-xs-6">
                            <?= $this->Form->input('groups._ids', ['options' => $groups]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group text">
                                <label for="capabilities-names">Capabilities</label>
                                <?php
                                    foreach ($capabilities as $k => $v) {
                                        echo $this->Form->input('capabilities[_names][' . $k .']', [
                                            'type' => 'checkbox',
                                            'label' => $v
                                        ]);
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
