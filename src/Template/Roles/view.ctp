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
                        <strong><?= __('Id') ?>:</strong>
                    </div>
                    <div class="col-xs-8 col-md-4"><?= h($role->id) ?></div>
                    <div class="col-xs-4 col-md-2 text-right">
                        <strong><?= __('Name') ?>:</strong>
                    </div>
                    <div class="col-xs-8 col-md-4"><?= h($role->name) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <h3><?= __('Associated Records'); ?></h3>
        <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#groups" aria-controls="groups" role="tab" data-toggle="tab">
                    <?= __('Groups'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#capabilities" aria-controls="capabilities" role="tab" data-toggle="tab">
                    <?= __('Capabilities'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="groups">
                <?php if (!empty($role->groups)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= $this->Paginator->sort(__('Id')) ?></th>
                                <th><?= $this->Paginator->sort(__('Name')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($role->groups as $groups): ?>
                            <tr>
                                <td><?= h($groups->id) ?></td>
                                <td><?= h($groups->name) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="capabilities">
                <?php if (!empty($role->capabilities)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= $this->Paginator->sort(__('Id')) ?></th>
                                <th><?= $this->Paginator->sort(__('Name')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($role->capabilities as $capabilities): ?>
                            <tr>
                                <td><?= h($capabilities->id) ?></td>
                                <td><?= h($capabilities->name) ?></td>
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
