<?php
declare(strict_types=1);

namespace RolesCapabilities;

use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;
use Cake\Event\EventManager;
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
        // Add middleware here.
        $middleware = parent::middleware($middleware);

        $middleware->add(new AuthorizationContextMiddleware());

        return $middleware;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap(PluginApplicationInterface $app)
    {
        // load default plugin config
        Configure::load('RolesCapabilities.roles_capabilities');

        $qf = (bool)Configure::read('RolesCapabilities.roles_capabilities.queryFilter', true);

        if ($qf) {
            $events = EventManager::instance();
            $events->on(new QueryFilterEventsListener());
        }
    }

    /**
     * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
     * @return void
     */
    public function routes($routes)
    {
        // Add routes.
        // By default will load `config/routes.php` in the plugin.
        parent::routes($routes);
    }
}
