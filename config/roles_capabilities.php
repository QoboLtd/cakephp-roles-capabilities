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
                'groups_users',
                'languages',
            ],
        ],
        'accessCheck' => [
            'skipControllers' => [
                'CakeDC\Users\Controller\SocialAccountsController',
                'App\Controller\PagesController'
            ],
            'skipActions' => [
                '*' => [
                    'getCapabilities',
                    'getSkipActions'
                ],
                'CakeDC\Users\Controller\UsersController' => [
                    'changePassword',
                    'failedSocialLogin',
                    'failedSocialLoginListener',
                    'getUsersTable',
                    'login',
                    'logout',
                    'register',
                    'requestResetPassword',
                    'resendTokenValidation',
                    'resetPassword',
                    'setUsersTable',
                    'socialEmail',
                    'socialLogin',
                    'twitterLogin',
                    'validate',
                    'validateEmail',
                    'validateReCaptcha',
                ],
            ],
            'defaultRules' => [
                'NoAuth',
                'SuperUser',
                'Permissions',
                'Capabilities'
            ],
            'assignationModels' => [
                'Users',
                'CakeDC/Users.Users'
            ],

        ],
        'Roles' => [
            'Admin' => [
                'name' => 'Admins',
                'description' => 'Administrators role',
                'deny_edit' => true,
                'deny_delete' => true
            ],
            'Everyone' => [
                'name' => 'Everyone',
                'description' => 'Generic role',
                'deny_edit' => false,
                'deny_delete' => true
            ]
        ]
    ]
];
