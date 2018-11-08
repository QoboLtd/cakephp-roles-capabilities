<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use RolesCapabilities\Event\Model\ModelBeforeFindEventsListener;

EventManager::instance()->on(new ModelBeforeFindEventsListener());

// load default plugin config
Configure::load('RolesCapabilities.roles_capabilities');
