<div class="row">
    <div class="col-xs-12">
        <h3><strong><?= $this->Html->link(__('Roles'), ['action' => 'index']) . ' &raquo; ' . h($role->name) ?></strong></h3>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">&nbsp;</h3>
            </div>
            <table class="table table-hover">
                <tr>
                    <td class="col-xs-3 text-right">
                        <strong><?= __('Id') ?>:</strong>
                    </td>
                    <td class="col-xs-3"><?= h($role->id) ?></td>
                    <td class="col-xs-3 text-right">
                        <strong><?= __('Name') ?>:</strong>
                    </td>
                    <td class="col-xs-3"><?= h($role->name) ?></td>
                </tr>
            </table>
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
                <?php endif; ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="capabilities">
                <?php if (!empty($role->capabilities)): ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
