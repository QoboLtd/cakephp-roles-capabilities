<?php
declare(strict_types=1);

namespace RolesCapabilities\EntityAccess\Event;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Table;

class ModelInitializeListener implements EventListenerInterface
{
    /**
     * {@inheritdoc}
     */
    public function implementedEvents()
    {
        return [
            'Model.initialize' => 'initializeEvent',
        ];
    }

    /**
     * Handler for initiaze event.
     *
     * @param Event $event The event to handle
     * @return void
     */
    public function initializeEvent(Event $event): void
    {
        $table = $event->getSubject();
        if (!($table instanceof Table)) {
            return;
        }

        if ($table->hasBehavior('Authorized')) {
            return;
        }

        $key = str_replace('.', '_', $table->getRegistryAlias());

        $config = Configure::read('RolesCapabilities.tables.' . $key);
        if ($config === null) {
            $config = Configure::read('RolesCapabilities.tables.*');
        }

        if ($config !== null) {
            if (!isset($config['enabled']) || $config['enabled'] === true) {
                unset($config['enabled']);
                $table->addBehavior('RolesCapabilities.Authorized', $config);
            }
        }
    }
}
