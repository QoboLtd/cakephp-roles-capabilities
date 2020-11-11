<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Cake\Event\EventManager;
use RolesCapabilities\EntityAccess\Event\QueryFilterEventsListener;
use RolesCapabilities\Event\Model\ModelBeforeFindEventsListener;

// load default plugin config
Configure::load('RolesCapabilities.roles_capabilities');

$events = EventManager::instance();

//$events->on(new ModelBeforeFindEventsListener());
$events->on(new QueryFilterEventsListener());
