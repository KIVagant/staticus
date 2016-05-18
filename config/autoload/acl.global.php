<?php

use Staticus\Acl\AclService;
use Staticus\Acl\Roles;

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
            \Staticus\Resources\File\ResourceDO::class => [
                AclService::PRIVILEGES => [
                    Roles::GUEST => [
                        \Staticus\Acl\Actions::ACTION_READ,
                    ],
                    Roles::ADMIN => [
                        \Staticus\Acl\Actions::ACTION_WRITE,
                        \Staticus\Acl\Actions::ACTION_SEARCH,
                        \Staticus\Acl\Actions::ACTION_DELETE,
                    ]
                ],
            ],
            \Staticus\Resources\Mpeg\ResourceDO::class => [
                AclService::INHERIT => \Staticus\Resources\File\ResourceDO::class,
            ],
            \Staticus\Resources\Image\ResourceImageDO::class => [
                AclService::INHERIT => \Staticus\Resources\File\ResourceDO::class,
            ],
            \Staticus\Resources\Jpg\ResourceDO::class => [
                AclService::INHERIT => \Staticus\Resources\Image\ResourceImageDO::class,
            ],
            \Staticus\Resources\Png\ResourceDO::class => [
                AclService::INHERIT => \Staticus\Resources\Image\ResourceImageDO::class,
            ],
            \Staticus\Resources\Gif\ResourceDO::class => [
                AclService::INHERIT => \Staticus\Resources\Image\ResourceImageDO::class,
            ],
        ],
    ],
];
