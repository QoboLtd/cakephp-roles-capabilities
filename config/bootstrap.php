<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use RolesCapabilities\Event\AddPermissionsListener;
use RolesCapabilities\Event\ModelBeforeFindEventsListener;

EventManager::instance()->on(new AddPermissionsListener());
EventManager::instance()->on(new ModelBeforeFindEventsListener());

// load default plugin config
Configure::load('RolesCapabilities.roles_capabilities');
