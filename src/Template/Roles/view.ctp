<section class="content-header">
    <h1><?= $this->Html->link(
        __('Users'),
        ['plugin' => 'RolesCapabilities', 'controller' => 'Roles', 'action' => 'index']
    ) . ' &raquo; ' . h($role->name) ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-unlock"></i>

                    <h3 class="box-title">Details</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt><?= __('Name') ?></dt>
                        <dd><?= h($role->name) ?></dd>
                        <dt><?= __('Description') ?></dt>
                        <dd><?= h($role->description) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <h2 class="page-header"><i class="fa fa-link"></i> <?= __('Associated Records'); ?></h2>
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#capabilities" aria-controls="capabilities" role="tab" data-toggle="tab">
                            <?= __('Capabilities'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#groups" aria-controls="groups" role="tab" data-toggle="tab">
                            <?= __('Groups'); ?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="capabilities">
                        <?php if (!empty($role->capabilities)) : ?>
                        <div class="row">
                            <?php
                            $setCapabilities = [];
                            foreach ($role->capabilities as $cap) {
                                $setCapabilities[] = $cap->name;
                            }
                            ksort($capabilities);
                            ?>
                            <?php foreach ($capabilities as $groupName => $groupCaps) : ?>
                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="form-group text">
                                    <label>
                                        <?= $this->cell('RolesCapabilities.Capability::groupName', [$groupName]) ?>
                                    </label>
                                    <?php
                                    asort($groupCaps);
                                    foreach ($groupCaps as $k => $v) {
                                        $checked = in_array($k, $setCapabilities);
                                        echo $this->Form->input('capabilities[_names][' . $k . ']', [
                                        'type' => 'checkbox',
                                        'checked' => $checked,
                                        'disabled' => true,
                                        'label' => $v,
                                        'div' => false
                                        ]);
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="groups">
                        <?php if (!empty($role->groups)) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-condensed table-vertical-align">
                                <thead>
                                    <tr>
                                        <th><?= __('Name') ?></th>
                                        <th><?= __('Description') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($role->groups as $groups) : ?>
                                    <tr>
                                        <td><?= $this->Html->link($groups->name, [
                                            'plugin' => 'Groups',
                                            'controller' => 'Groups',
                                            'action' => 'view',
                                            $groups->id
                                        ]) ?></td>
                                        <td><?= h($groups->description) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>