<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use RolesCapabilities\EntityAccess\Event\QueryFilterEventsListener;
use RolesCapabilities\Event\Model\ModelBeforeFindEventsListener;

EventManager::instance()->on(new ModelBeforeFindEventsListener());
EventManager::instance()->on(new QueryFilterEventsListener());

// load default plugin config
Configure::load('RolesCapabilities.roles_capabilities');
