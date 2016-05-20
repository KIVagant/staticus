<?php

use Staticus\Acl\AclService;
use Staticus\Acl\Roles;
use Staticus\Auth\UserInterface;
use Staticus\Resources\ResourceDOInterface;

return [
    'dependencies' => [
        'invokables' => [
            Zend\Permissions\Acl\AclInterface::class => Zend\Permissions\Acl\Acl::class,
            Staticus\Acl\AclServiceInterface::class => Staticus\Acl\AclService::class,
            Staticus\Auth\UserInterface::class => Staticus\Auth\User::class,
        ],
        'types' => [
            Zend\Permissions\Acl\AclInterface::class => Zend\Permissions\Acl\AclInterface::class,
            Staticus\Acl\AclServiceInterface::class => Staticus\Acl\AclServiceInterface::class,
            Staticus\Auth\UserInterface::class => Staticus\Auth\UserInterface::class,
        ],
    ],
    'acl' => [
        AclService::ROLES => [
            /* Roles registry.

                // Use any role names, from predefined constants or your own
                'MY_ROLE' => [
                    // Role options can placed here. By default no options needed.
                ],
                'ANOTHER_ROLE' => [
                    AclService::INHERIT => [
                        'MY_ROLE',  // give all parent 'MY_ROLE' permissions for all resources by default
                    ],
                ],
                Roles::ADMIN => [
                    // ...
                ],
             */

            Roles::GUEST => [],
            Roles::USER => [
                AclService::INHERIT => [
                    Roles::GUEST,
                ],
            ],
            Roles::ADMIN => [
                AclService::INHERIT => [
                    Roles::GUEST,
                    Roles::USER,
                ],
            ],
        ],
        AclService::RESOURCES => [
            /* Resources registry

                    // place here any unique string that describes your resource
                   'my.acl.resource.unique' => [

                        // Place here the list of allowed actions for each registered roles (except inherits, of course).
                        AclService::PRIVILEGES => [
                            'MY_ROLE' => [

                                // You can write here any string action or use predefined constants:
                                Staticus\Acl\Actions::ACTION_READ,
                                'search',
                                'getAllowedFilters',
                            ],
                        ],
                    ],
                    'my.acl.resource.another' => [
                        // extend all parent permissions
                        AclService::INHERIT => 'my.acl.resource.unique',
                        AclService::PRIVILEGES => [
                            // add other privileges if needed
                        ],
                    ],
            */
            // ---------------------------------------------------------------------------------------------------------
            /** @see \Staticus\Acl\AclMiddleware that uses this config */
            // ADMIN RULES
            Staticus\Resources\File\ResourceDO::class => [
                AclService::PRIVILEGES => [
                    Roles::ADMIN => [
                        Staticus\Acl\Actions::ACTION_READ,
                        Staticus\Acl\Actions::ACTION_WRITE,
                        Staticus\Acl\Actions::ACTION_SEARCH,
                        Staticus\Acl\Actions::ACTION_DELETE,
                    ]
                ],
            ],
            Staticus\Resources\Mpeg\ResourceDO::class => [
                AclService::INHERIT => Staticus\Resources\File\ResourceDO::class,
            ],
            Staticus\Resources\Image\ResourceImageDO::class => [
                AclService::INHERIT => Staticus\Resources\File\ResourceDO::class,
            ],
            Staticus\Resources\Jpg\ResourceDO::class => [
                AclService::INHERIT => Staticus\Resources\Image\ResourceImageDO::class,
            ],
            Staticus\Resources\Png\ResourceDO::class => [
                AclService::INHERIT => Staticus\Resources\Image\ResourceImageDO::class,
            ],
            Staticus\Resources\Gif\ResourceDO::class => [
                AclService::INHERIT => Staticus\Resources\Image\ResourceImageDO::class,
            ],

            // ---------------------------------------------------------------------------------------------------------
            // Rules for the common resources namespace
            ResourceDOInterface::NAMESPACES_WILDCARD . Staticus\Resources\File\ResourceDO::class => [
                AclService::PRIVILEGES => [
                    Roles::GUEST => [
                        Staticus\Acl\Actions::ACTION_READ,
                    ],
                ],
            ],
            ResourceDOInterface::NAMESPACES_WILDCARD . Staticus\Resources\Mpeg\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\File\ResourceDO::class,
            ],
            ResourceDOInterface::NAMESPACES_WILDCARD . Staticus\Resources\Image\ResourceImageDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\File\ResourceDO::class,
            ],
            ResourceDOInterface::NAMESPACES_WILDCARD . Staticus\Resources\Jpg\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],
            ResourceDOInterface::NAMESPACES_WILDCARD . Staticus\Resources\Png\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],
            ResourceDOInterface::NAMESPACES_WILDCARD . Staticus\Resources\Gif\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],

            // ---------------------------------------------------------------------------------------------------------
            // Rules inside home namespace for user
            UserInterface::NAMESPACES_WILDCARD . Staticus\Resources\File\ResourceDO::class => [
                AclService::PRIVILEGES => [
                    // Guests can see users files
                    Roles::GUEST => [
                        Staticus\Acl\Actions::ACTION_READ,
                    ],

                    // Users can modify users files
                    Roles::USER => [
                        Staticus\Acl\Actions::ACTION_WRITE,
                        Staticus\Acl\Actions::ACTION_SEARCH,
                        Staticus\Acl\Actions::ACTION_DELETE,
                    ],
                ],
            ],
            UserInterface::NAMESPACES_WILDCARD . Staticus\Resources\Mpeg\ResourceDO::class => [
                AclService::INHERIT => UserInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\File\ResourceDO::class,
            ],
            UserInterface::NAMESPACES_WILDCARD . Staticus\Resources\Image\ResourceImageDO::class => [
                AclService::INHERIT => UserInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\File\ResourceDO::class,
            ],
            UserInterface::NAMESPACES_WILDCARD . Staticus\Resources\Jpg\ResourceDO::class => [
                AclService::INHERIT => UserInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],
            UserInterface::NAMESPACES_WILDCARD . Staticus\Resources\Png\ResourceDO::class => [
                AclService::INHERIT => UserInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],
            UserInterface::NAMESPACES_WILDCARD . Staticus\Resources\Gif\ResourceDO::class => [
                AclService::INHERIT => UserInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],

            // ---------------------------------------------------------------------------------------------------------
            // CUSTOM NAMESPACES below

            // ---------------------------------------------------------------------------------------------------------
            // FRACTAL namespace
            'fractal' . Staticus\Resources\File\ResourceDO::class => [
                AclService::PRIVILEGES => [
                    Roles::GUEST => [
                        Staticus\Acl\Actions::ACTION_READ,
                    ],
                ],
            ],
            'fractal' . Staticus\Resources\Mpeg\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\File\ResourceDO::class,
            ],
            'fractal' . Staticus\Resources\Image\ResourceImageDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\File\ResourceDO::class,
            ],
            'fractal' . Staticus\Resources\Jpg\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],
            'fractal' . Staticus\Resources\Png\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],
            'fractal' . Staticus\Resources\Gif\ResourceDO::class => [
                AclService::INHERIT => ResourceDOInterface::NAMESPACES_WILDCARD
                    . Staticus\Resources\Image\ResourceImageDO::class,
            ],
        ],
    ],
];
