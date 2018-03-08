<?php

use Cake\Utility\Inflector;

$count = 0;
$tabs = '';
?>
<div class="row">
    <div class="col-md-2">
        <div style="height: 500px; overflow-x:hidden; overflow-y:scroll;">
        <ul class="nav nav-pills nav-stacked">
        <?php ksort($capabilities); foreach ($capabilities as $groupName => $groupCaps) : ?>
            <?php
            if (empty($groupCaps)) {
                continue;
            }
            $active = ++$count == 1 ? 'active' : '';
            $tabId = Inflector::underscore(preg_replace('/\\\/', '', $groupName));
            $tabs .= '<div id="' . $tabId . '" class="tab-pane ' . $active . '">';

            $subtabs = '';
            $subtabs_menu = '<ul class="nav nav-tabs">';

            $sCount = 0;
            foreach ($groupCaps as $type => $caps) {
                usort($caps, function ($a, $b) {
                    return strcmp($a->getDescription(), $b->getDescription());
                });

                $title = Inflector::humanize($type) . ' ' . __('Access');
                $slug = $tabId . '_' . $type . '_' . 'access';

                $sActive = ++$sCount == 1 ? 'active' : '';
                $subtabs_menu .= '<li class="' . $sActive . '"><a href="#' . $slug . '" data-toggle="tab">' . $title . '</a>';
                $subtabs .= '<div id="' . $slug . '" class="tab-pane ' . $sActive . '">';

                foreach ($caps as $cap) {
                    $subtabs .= $this->Form->input($cap->getName(), [
                        'type' => 'checkbox',
                        'label' => $cap->getDescription(),
                        'class' => 'checkbox-capability',
                        'div' => false,
                        'disabled' => $disabled,
                        'checked' => in_array($cap->getName(), $roleCaps)
                    ]);
                }
                $subtabs .= '</div>';
            }

            $subtabs_menu .= '</ul>';
            $tabs .= $subtabs_menu . '<div class="tab-content clearfix">' . $subtabs . "</div>";
            $tabs .= '</div>';
            ?>
            <li class="<?= $active ?>"><a href="#<?= $tabId ?>" data-toggle="tab"><?= $this->cell('RolesCapabilities.Capability::groupName', [$groupName]) ?></a></li>
        <?php endforeach; ?>
        </ul>
        </div>
    </div>
    <div class="col-md-10">
        <div class="tab-content clearfix">
            <?= $tabs ?>
        </div>
    </div>
</div>

