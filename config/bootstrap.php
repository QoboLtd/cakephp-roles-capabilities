<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use RolesCapabilities\Event\ModelBeforeFindEventsListener;
use RolesCapabilities\Event\AddPersonalPermissionsListener;

EventManager::instance()->on(new ModelBeforeFindEventsListener());
EventManager::instance()->on(new AddPersonalPermissionsListener());

// load default plugin config
Configure::load('RolesCapabilities.roles_capabilities');
