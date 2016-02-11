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
bin/cake migrations migrate -p Groups
```

Load the plugin in your config/bootstrap.php file:

```
Plugin::load('RolesCapabilities', ['bootstrap' => false, 'routes' => true, 'autoload' => true]);
```

Load the Capability component in your src/Controller/AppController.php file, from the `initialize()` method:

```
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('RolesCapabilities.Capability');
    }
```

## Configuration

In your Controller import `Capability` class with `use RolesCapabilities\Capability` and create a static method `getCapabilities()` which it will return an array of `Capability` objects.

The `Capability` object accepts two parameters. The first (and required) parameter is the `Capability` `name` (string), which has to be unique throughout your application. The second (and optional) parameter is the `Capability` `options` (array) in which you can add parameters like `label` and `description`.

Finally in your Controller methods you can run the access check as shown in the `index()` method and throw an appropriate exception or handle however you see fit.

```
use RolesCapabilities\Capability;

class PostsController extends AppController
{
    const CAP_POSTS_INDEX = 'cap_posts_index';

    public static function getCapabilities()
    {
        return [
            new Capability(static::CAP_POSTS_INDEX, [
                'label' => 'Cap Posts Index'
                'description' => 'Allow listing posts'
            ])
        ];
    }

    public function index()
    {
        if (!$this->Capability->hasAccess(static::CAP_POSTS_INDEX)) {
            throw new ForbiddenException();
        }

        // method's logic
    }
}
```
