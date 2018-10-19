<?php
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Qobo\RolesCapabilities\Event\ModelBeforeFindEventsListener;

EventManager::instance()->on(new ModelBeforeFindEventsListener());

// load default plugin config
Configure::load('Qobo/RolesCapabilities.roles_capabilities');
