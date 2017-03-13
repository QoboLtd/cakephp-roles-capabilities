<?php
// Roles and Capabilities plugin configuration
return [
    'RolesCapabilities' => [
        'ownerCheck' => [
            // List of tables that should be skipped during record access check, to avoid infinite recursion.
            'skipTables' => [
                'roles',
                'capabilities',
                'users',
                'groups',
                'groups_roles',
                'groups_users'
            ],
            'skipControllers' => [
                'CakeDC\Users\Controller\SocialAccountsController',
                'App\Controller\PagesController'
            ],
            'skipActions' => [
                'login', 
                'logout'
            ]      
        ]
    ]        
];
