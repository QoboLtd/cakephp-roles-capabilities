<div class="row">
    <div class="col-xs-12">
        <h3><strong><?= $this->Html->link(__('Roles'), ['action' => 'index']) . ' &raquo; ' . h($role->name) ?></strong></h3>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">&nbsp;</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-4 col-md-2 text-right">
                        <strong><?= __('Name') ?>:</strong>
                    </div>
                    <div class="col-xs-8 col-md-4"><?= h($role->name) ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-4 col-md-2 text-right">
                        <strong><?= __('Description') ?>:</strong>
                    </div>
                    <div class="col-xs-8 col-md-4"><?= h($role->description) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
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
                <?php if (!empty($role->capabilities)): ?>
             <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Capabilities') ?></h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?php
                        $setCapabilities = [];
                        foreach ($role->capabilities as $cap) {
                            $setCapabilities[] = $cap->name;
                        }
                        ksort($capabilities);
                        foreach ($capabilities as $group_name => $group_caps) :
                    ?>
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="form-group text">
                                <label><?= $this->cell('RolesCapabilities.Capability::groupName', [$group_name]) ?></label>
                            <?php
                                asort($group_caps);
                                foreach ($group_caps as $k => $v) {
                                    $checked = in_array($k, $setCapabilities);
                                    echo $this->Form->input('capabilities[_names][' . $k .']', [
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
                </div>
            </div>

                <?php endif; ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="groups">
                <div class="table-responsive">
                    <table class="table table-hover">
                <?php if (!empty($role->groups)): ?>
                        <thead>
                            <tr>
                                <th><?= __('Name') ?></th>
                                <th><?= __('Description') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($role->groups as $groups): ?>
                            <tr>
                                <td><?= $this->Html->link(h($groups->name), '/groups/groups/view/' . $groups->id); ?></td>
                                <td><?= h($groups->description) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                <?php else: ?>
                    <tbody>
                        <td>
                            <p class="bg-warning">This role is not assigned to any groups.</p>
                        </td>
                    </tbody>
                <?php endif; ?>
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>
