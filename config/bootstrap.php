<?php
use Cake\Event\EventManager;
use RolesCapabilities\Event\ModelBeforeFindEventsListener;

EventManager::instance()->on(new ModelBeforeFindEventsListener());
