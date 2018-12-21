<?php

return [
    'display_name'    => 'Codex',
    'description'     => 'Codex is a file-based documentation platform built on top of Laravel. It\'s completely customizable and dead simple to use to create beautiful documentation.',
    'default_project' => 'codex',
    'addons'          => [],
    'paths'           => [
        'docs' => __DIR__ . '/../docs',
    ],
    'projects'        => [
        'casts'    => [
            'meta'       => 'collection',
            'layout'     => 'collection',
            'processors' => 'collection',
            'revision'   => 'collection',
            'document'   => 'collection',
        ],
        'defaults' => [
            'meta' => [
                'icon'        => 'fa-book',
                'color'       => 'deep-orange',
                'authors'     => [
                    [ 'name' => 'Robin Radic', 'email' => 'rradic@hotmail.com' ],
                ],
                'license'     => 'MIT',
                'links'       => [
                    'Git'     => 'https://github.com/codex-project',
                    'Issues'  => 'https://github.com/codex-project/codex/issues',
                    'Package' => 'https://packagist.com/codex-project/codex',
                ],
                'stylesheets' => [],
                'javascripts' => [],
                'styles'      => [],
                'scripts'     => [],
            ],

            'disk'         => null,
            'display_name' => null,
            'description'  => '',
            'processors'   => [ 'enabled' => [], 'disabled' => [], ],

            'revision' => [
                'default'              => 'master',
                'allow_php_config'     => false,
                'allowed_config_files' => [ 'revision.yml' ],
            ],

            'document' => [
                'default'    => 'index',
                'extensions' => [ 'md', 'markdown' ],
                'view'       => 'codex::layouts.default',
                'cache'      => [
                    'mode'    => null,
                    'minutes' => 7,
                ],
            ],
        ],
        'inherits' => [
            'layout',
            'processors',
        ],
    ],

    'revisions' => [
        'casts'    => [],
        'inherits' => [
            'layout',
            'document',
            'processors',
            'meta',
        ],
        // the default values of codex.yml
        'defaults' => [
            // inherits from project:
            // layout
            // index
            // processors
            // view
        ],
    ],

    'documents' => [
        'casts'    => [],
        'inherits' => [
            'layout',
            'processors',
            'view',
            'meta',
        ],
        'defaults' => [
            'title'       => '',
            'subtitle'    => '',
            'description' => '',
            'cache'       => true,
            // inherits from refs codex.yml
            // processors
            // view
        ],
    ],
];
