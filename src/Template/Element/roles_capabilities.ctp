<?php

use Cake\Utility\Inflector;

$count = 0;
$tabs = '';
ksort($capabilities);
?>
<div class="row">
    <div class="col-md-2">
        <div class="fixed-height-box">
        <ul class="nav nav-pills nav-stacked">
        <?php foreach ($capabilities as $groupName => $groupCaps) : ?>
            <?php
            if (empty($groupCaps)) {
                continue;
            }
                $active = ++$count == 1 ? 'active' : '';
                $tabId = Inflector::underscore(preg_replace('/\\\/', '', $groupName));
            ?>
            <li class="<?= $active ?>"><a href="#<?= $tabId ?>" data-toggle="tab"><?= $this->cell('RolesCapabilities.Capability::groupName', [$groupName]) ?></a></li>
        <?php endforeach; ?>
        </ul>
        </div>
    </div>
    <div class="col-md-10">
        <div class="tab-content clearfix">
        <?php $count = 0; ?>
        <?php foreach ($capabilities as $groupName => $groupCaps) : ?>
            <?php
            if (empty($groupCaps)) {
                continue;
            }
            $active = ++$count == 1 ? 'active' : '';
            $tabId = Inflector::underscore(preg_replace('/\\\/', '', $groupName));
        ?>
        <div id="<?= $tabId ?>" class="tab-pane <?= $active ?>">
        <ul class="nav nav-tabs">
        <?php
            $sCount = 0;
        foreach ($groupCaps as $type => $caps) {
            usort($caps, function ($a, $b) {
                return strcmp($a->getDescription(), $b->getDescription());
            });

            $title = Inflector::humanize($type) . ' ' . __('Access');
            $type = preg_replace('/(\(|\))/', '', $type);
            $slug = $tabId . '_' . $type . '_' . 'access';

            $sActive = ++$sCount == 1 ? 'active' : '';
            echo '<li class="' . $sActive . '"><a href="#' . $slug . '" data-toggle="tab">' . $title . '</a>';
        }
        ?>
        </ul>
        <div class="tab-content clearfix">
        <?php
            $sCount = 0;
        foreach ($groupCaps as $type => $caps) {
            usort($caps, function ($a, $b) {
                return strcmp($a->getDescription(), $b->getDescription());
            });

            $title = Inflector::humanize($type) . ' ' . __('Access');
            $type = preg_replace('/(\(|\))/', '', $type);
            $slug = $tabId . '_' . $type . '_' . 'access';

            $sActive = ++$sCount == 1 ? 'active' : '';
            echo '<div id="' . $slug . '" class="tab-pane ' . $sActive . '">';

            echo $this->Form->input($slug, [
                'type' => 'checkbox',
                'label' => 'Select All',
                'class' => 'select_all',
                'div' => false,
                'disabled' => $disabled,
            ]);
            echo '<hr/>';

            foreach ($caps as $cap) {
                echo $this->Form->input($cap->getName(), [
                    'type' => 'checkbox',
                    'label' => $cap->getDescription(),
                    'class' => 'checkbox-capability',
                    'div' => false,
                    'disabled' => $disabled,
                    'checked' => in_array($cap->getName(), $roleCaps)
                ]);
            }
            echo '</div>';
        }
        ?>
        </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
