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

            // ---------------------------------------------------------------------------------------------------------
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
