<?php
// Roles and Capabilities plugin configuration
return [
    'RolesCapabilities' => [
        'ownerCheck' => [
            // List of tables that should be skipped during record access check, to avoid infinite recursion.
            'skipTables' => [
                'byInstance' => [
                    CakeDC\Users\Model\Table\UsersTable::class,
                    Groups\Model\Table\GroupsTable::class,
                    RolesCapabilities\Model\Table\CapabilitiesTable::class,
                    RolesCapabilities\Model\Table\RolesTable::class,
                ],
                'byTableName' => [],
                'byRegistryAlias' => [
                    'GroupsRoles',
                    'GroupsUsers',
                ],
            ],
        ],
        'accessCheck' => [
            'skipControllers' => [
                'CakeDC\Users\Controller\SocialAccountsController',
                'App\Controller\PagesController',
            ],
            'skipActions' => [
                '*' => [
                    'getCapabilities',
                    'getSkipActions',
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
                RolesCapabilities\Access\NoAuthAccess::class,
                RolesCapabilities\Access\SuperUserAccess::class,
                RolesCapabilities\Access\PermissionsAccess::class,
                RolesCapabilities\Access\CapabilitiesAccess::class,
                RolesCapabilities\Access\SupervisorAccess::class,
            ],
            'assignationModels' => [
                'Users',
                'CakeDC/Users.Users',
            ],
            'belongsToModels' => [
                'Groups',
            ],
        ],
        'Roles' => [
            'Admin' => [
                'name' => 'Admins',
                'description' => 'Administrators role',
                'deny_edit' => true,
                'deny_delete' => true,
            ],
            'Everyone' => [
                'name' => 'Everyone',
                'description' => 'Generic role',
                'deny_edit' => false,
                'deny_delete' => true,
            ],
        ],
    ],
];
