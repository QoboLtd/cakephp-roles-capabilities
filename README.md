# Roles and Capabilities plugin for CakePHP

[![Build Status](https://travis-ci.org/QoboLtd/cakephp-roles-capabilities.svg?branch=master)](https://travis-ci.org/QoboLtd/cakephp-roles-capabilities)
[![Latest Stable Version](https://poser.pugx.org/qobo/cakephp-roles-capabilities/v/stable)](https://packagist.org/packages/qobo/cakephp-roles-capabilities)
[![Total Downloads](https://poser.pugx.org/qobo/cakephp-roles-capabilities/downloads)](https://packagist.org/packages/qobo/cakephp-roles-capabilities)
[![Latest Unstable Version](https://poser.pugx.org/qobo/cakephp-roles-capabilities/v/unstable)](https://packagist.org/packages/qobo/cakephp-roles-capabilities)
[![License](https://poser.pugx.org/qobo/cakephp-roles-capabilities/license)](https://packagist.org/packages/qobo/cakephp-roles-capabilities)
[![codecov](https://codecov.io/gh/QoboLtd/cakephp-roles-capabilities/branch/master/graph/badge.svg)](https://codecov.io/gh/QoboLtd/cakephp-roles-capabilities)
[![BCH compliance](https://bettercodehub.com/edge/badge/QoboLtd/cakephp-roles-capabilitie?branch=master)](https://bettercodehub.com/)

## About

CakePHP 3+ plugin managing user roles and capabilities.

This plugin is developed by [Qobo](https://www.qobo.biz) for [Qobrix](https://qobrix.com).  It can be used as standalone CakePHP plugin, or as part of the [project-template-cakephp](https://github.com/QoboLtd/project-template-cakephp) installation.

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

Run required plugin(s) migration task:

```
bin/cake migrations migrate -p Groups
```

## Setup
Load plugin
```
bin/cake plugin load RolesCapabilities
```

Load required plugin(s)
```
bin/cake plugin load Muffin/Trash
bin/cake plugin load --routes --bootstrap CakeDC/Users
```

### Middleware
The AuthorizationContextMiddleware is registered your middleware queue
and if you are using an Authentication middleware or the AuthComponent with stateless sessions it should be configured automatically.
If not see the next section:

### Manual Context 
You need to call the following code in your controller code to configure the AuthorizationContext.

```
use RolesCapabilities\EntityAccess\AuthorizationContext;
use RolesCapabilities\EntityAccess\AuthorizationContextHolder;
use RolesCapabilities\EntityAccess\UserWrapper;

...

    public function setUser($user, $request)
    {
        if (!empty($user)) {
            AuthorizationContextHolder::push(AuthorizationContext::asUser(UserWrapper::forUser($user), $request);
        } else {
            AuthorizationContextHolder::push(AuthorizationContext::asAnonymous($request);
        }
    }
```

### Controller authorization

If you are using AuthComponent you can use the 'RolesCapabilities.EntityAccess'
authorization adapter. The authorization adapter will authorize access to the
controller depending on the access on the undelying model.

By default the id of the entity is obtained from the 'id' request parameter.
You can change it with the idParam config option.

### Model authorization
Model (table) authorization works using the RolesCapabilities.Authorized behavior.
You can either add it manually or through RolesCapabilities.tables configuration.

For each table you can create one entry or add an * entry to match all tables.

### Options

```
[
    'associations' => [
        'AssignedRoles' => [ 'association' => 'Groups.Users' ],
    ],
    'capabilities' => [
        ['operation' => Operation::VIEW, 'association' => 'AssignedRoles' ],
    ],
    // Additional operations (only used for controller authorization)
    'operations' => [
        'export',
    ],
    // Users table etc.
    'users' => [
        'table' => 'Users',
    ],
]
```