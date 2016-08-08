# Roles and Capabilities plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

This plugin works along with [Qobo Groups plugin](https://github.com/QoboLtd/cakephp-groups).

The recommended way to install composer packages is:

```
composer require qobo/cakephp-roles-capabilities
```

Run plugin's migration task:

```
bin/cake migrations migrate -p RolesCapabilities
```

Load the plugin in your config/bootstrap.php file:

```
Plugin::load('RolesCapabilities', ['bootstrap' => true, 'routes' => true]);
```

Load the Capability component in your src/Controller/AppController.php file using the `initialize()` method. Additionally use the CapabilityTrait in AppController. See details below:

```
use RolesCapabilities\CapabilityTrait;

class AppController extends Controller
{
    use CapabilityTrait;

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('RolesCapabilities.Capability', [
            'currentRequest' => $this->request->params
        ]);
    }
```
