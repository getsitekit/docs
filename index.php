<?php

use Sitekit\Docs\Event\RouteListener;

return [

    'name' => 'docs',

    'autoload' => [

        'Sitekit\\Docs\\' => 'src'

    ],

    'nodes' => [

        'docs' => [
            'name' => '@docs',
            'label' => 'Docs',
            'protected' => true,
            'frontpage' => true,
            'controller' => 'Sitekit\\Docs\\Controller\\SiteController'
        ]

    ],

    'routes' => [

        '/docs' => [
            'name' => '@docs',
            'controller' => [
                'Sitekit\\Docs\\Controller\\DocsController',
                'Sitekit\\Docs\\Controller\\ApiDocsController'
            ]
        ]

    ],

    'permissions' => [

        'docs: manage docs' => [
            'title' => 'Manage docs',
            'description' => 'Create, edit, delete and publish docs'
        ]

    ],

    'menu' => [

        'docs' => [
            'priority' => 110,
            'label' => 'Docs',
            'icon' => 'docs:icon.svg',
            'url' => '@docs/admin/post',
            'active' => '@docs/admin/post*',
            'access' => 'docs: manage docs || system: access settings'
        ],
        'docs: index' => [
            'parent' => 'docs',
            'priority' => 0,
            'label' => 'Docs',
            'url' => '@docs/admin/post',
            'active' => '@docs/admin/post*',
            'access' => 'docs: manage docs'
        ],
        'docs: settings' => [
            'label' => 'Settings',
            'parent' => 'docs',
            'url' => '@docs/admin/settings',
            'active' => '@docs/admin/settings*',
            'access' => 'system: access settings'
        ]

    ],

    'settings' => '@docs/admin/settings',

    'config' => [

        'posts' => [
            'posts_per_page' => 20,
        ],
        'permalink' => [
            'type' => '',
            'custom' => '{slug}'
        ]

    ],

    'events' => [

        'boot' => function ($event, $app) {
            $app->subscribe(
                new RouteListener
            );
        }

    ]

];
