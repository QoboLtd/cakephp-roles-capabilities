<section class="content-header">
    <h1><?= $this->Html->link(
        __('Personal Permission'),
        ['plugin' => 'RolesCapabilities', 'controller' => 'PersonalPermissions', 'action' => 'index']
    ) . ' &raquo; ' . h($personalPermission->model) ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-shield"></i>

                    <h3 class="box-title">Details</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt><?= __('Model') ?></dt>
                        <dd><?= h($personalPermission->model) ?></dd>
                        <dt><?= __('Type') ?></dt>
                        <dd><?= h($personalPermission->type) ?></dd>
                        <dt><?= __('User') ?></dt>
                        <dd><?= h($personalPermission->user->username) ?></dd>
                        <dt><?= __('Status') ?></dt>
                        <dd><?= h($personalPermission->is_active ? 'Active' : 'Disabled') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</section>
