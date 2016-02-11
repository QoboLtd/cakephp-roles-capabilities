<?php
use Cake\Routing\Router;

Router::plugin(
    'RolesCapabilities',
    ['path' => '/roles-capabilities'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
