<?php
declare(strict_types=1);

namespace RolesCapabilities;

use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Routing\RouteBuilder;
use RolesCapabilities\EntityAccess\Event\ModelInitializeListener;
use RolesCapabilities\EntityAccess\Event\QueryFilterEventsListener;
use RolesCapabilities\Middleware\AuthorizationContextMiddleware;

class Plugin extends BasePlugin
{

    /**
     * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to update.
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware($middleware)
    {
        $middleware->add(new AuthorizationContextMiddleware());

        return $middleware;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $events = EventManager::instance();
        $events->on(new ModelInitializeListener());
    }

    /**
     * @param RouteBuilder $routes The route builder to update.
     * @return void
     */
    public function routes($routes): void
    {
        $routes->plugin(
            'RolesCapabilities',
            ['path' => '/roles-capabilities'],
            function ($routes) {
                $routes->fallbacks('DashedRoute');
            }
        );
    }
}
