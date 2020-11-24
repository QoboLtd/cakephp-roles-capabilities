<?php

use Cake\Utility\Inflector;

$count = 0;
$tabs = '';
ksort($capabilities);

function in_cap_array(array $capArray, string $resource, string $operation, string $association): bool
{
    foreach ($capArray as $cap) {
        if (isset($cap['resource']) && $cap['resource'] !== $resource) {
            continue;
        }

        if ($cap['operation'] === $operation
           && $cap['association'] === $association
        ) {
               return true;
        }
    }
    return false;
}
?>
<div class="row">
    <div class="col-md-2">
        <div class="fixed-height-box">
        <ul class="nav nav-pills nav-stacked">
        <?php // List tables  
            foreach ($capabilities as $tableName => $tableCaps) : ?>
            <?php
                if (empty($tableCaps)) {
                    continue;
                }

                $active = ++$count == 1 ? 'active' : '';
                $tabId = Inflector::underscore(preg_replace('/[^a-zA-Z0-9]+/', '_', $tableName));
            ?>
            <li class="<?= $active ?>"><a href="#<?= $tabId ?>" data-toggle="tab"><?= Inflector::humanize(Inflector::underscore($tableName)) ?></a></li>
        <?php endforeach; ?>
        </ul>
        </div>
    </div>
    <div class="col-md-10">
        <div class="tab-content clearfix">
        <?php $count = 0; ?>
        <?php 
        foreach ($capabilities as $tableName => $tableCaps) : ?>
            <?php
            if (empty($tableCaps)) {
                continue;
            }
                $active = ++$count == 1 ? 'active' : '';
                $tabId = Inflector::underscore(preg_replace('/[^a-zA-Z0-9]+/', '_', $tableName));
            ?>
            <div id="<?= $tabId ?>" class="tab-pane <?= $active ?>">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <?php foreach ($tableCaps['associations'] as $name => $value) : ?>
                        <th><?= Inflector::humanize(Inflector::underscore($name)) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <?php foreach ($tableCaps['operations'] as $operation) : ?>
            <tr>
                <td><?= Inflector::humanize(Inflector::underscore($operation))?></td>
                <?php foreach ($tableCaps['associations'] as $name => $association) : 
                    $inputId = str_replace('.', '_', $tableName) .'@' . $operation . '@'. $name;
                    $implied = in_cap_array($tableCaps['capabilities'], $tableName, $operation, $name);
                    $checked = $implied || in_cap_array($roleCaps, $tableName, $operation, $name);
                ?>
                <td>
                     <?= $this->Form->checkbox($inputId, [
                        'class' => 'checkbox-capability',
                        'disabled' => $implied,
                        'checked' => $checked
                    ])
                    ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            </table>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
