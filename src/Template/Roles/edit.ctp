<section class="content-header">
    <h1><?= __('Edit {0}', ['Role']) ?></h1>
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
                <div class="col-xs-12">
                    <?= $this->Form->label(__('Groups')); ?>
                    <div class="row">
                        <?php foreach ($groups as $k => $v) : ?>
                        <div class="col-xs-4 col-md-2">
                            <?= $this->Form->select('groups._ids', [$k => $v], [
                                'multiple' => 'checkbox',
                                'hiddenField' => false
                            ]); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Capabilities') ?></h3>
			
        </div>

		<?php $count = 0; $max = 3; ?>
        <div class="box-body">
           	<div class="row">
            <?php ksort($capabilities); foreach ($capabilities as $groupName => $groupCaps) : ?>
				<?php if ($count > $max) : ?>
					</div>
					<div class="row">
					<?php $count = 0; ?>
				<?php endif; ?> 
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
					<div class="box box-default collapsed-box">
						<div class="box-header with-border">
							<h3 class="box-title"><?= $this->cell('RolesCapabilities.Capability::groupName', [$groupName]) ?></h3>
							<div class="box-tools pull-right">
								<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
							</div>
						</div>
						<div class="box-body">
							<?php
								asort($groupCaps);
								foreach ($groupCaps as $k => $v) {
									echo $this->Form->input('capabilities[_names][' . $k . ']', [
										'type' => 'checkbox',
										'label' => $v,
										'div' => false,
										'checked' => in_array($k, $roleCaps)
									]);
								}
							?>
						</div>
					</div>
				</div>
				<?php $count++; ?>
            <?php endforeach; ?>
            </div>
			
        </div>
        <div class="box-footer">
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
	
    <?= $this->Form->end() ?>
</section>
