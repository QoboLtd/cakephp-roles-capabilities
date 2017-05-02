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
                    'failedSocialLogin',
                    'failedSocialLoginListener',
                    'getUsersTable',
                    'login',
                    'logout',
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
                ]
            ],
            'defaultRules' => [
                'SuperUser',
                'Permissions',
                'Capabilities'
            ],
            'assignationModels' => [
                'Users',
                'CakeDC/Users.Users'
            ],

        ],
    ]
];
