<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Display Name
    |--------------------------------------------------------------------------
    |
    | This will be used for the <title> and for the header
    |
    */
    'display_name'    => env('CODEX_DISPLAY_NAME', config('app.name', 'Codex')),

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    |
    | Describes the application
    |
    */
    'description'     => 'Codex is a file-based documentation platform built on top of Laravel. It\'s completely customizable and dead simple to use to create beautiful documentation.',

    /*
    |--------------------------------------------------------------------------
    | Default Project
    |--------------------------------------------------------------------------
    |
    | Will be used when, for example, you do not specify the project name in the URL
    |
    */
    'default_project' => env('CODEX_DEFAULT_PROJECT', 'codex'),

    'paths' => [
        'docs' => resource_path('docs'),
    ],

    'http' => [
        // run codex under a specific uri. For example, setting this to 'foobar' will result in urls like
        // http://host.com/foobar/documentation/$PROJECT/$REVISION/$DOCUMENT
        // http://host.com/foobar/graphql
        // you can leave this to null to not have a base_route
        'prefix'               => env('CODEX_ROUTING_PREFIX', null),
        'api_prefix'           => 'graphql',
        'documentation_prefix' => 'documentation',
        'documentation_view'   => 'codex::index',
    ],

    'processors' => [
        'enabled'  => [],
        'disabled' => [],
    ],

    'projects' => [
        'meta' => [
            'icon'        => 'fa-book',
            'color'       => 'deep-orange',
            'authors'     => [], //['name' => 'Robin Radic', 'email' => 'rradic@hotmail.com']
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

        'view'  => 'codex::partials.document',
        'cache' => [
            // true     = enabled
            // false    = disabled
            // null     = disabled when app.debug is true
            'mode'    => null, // \Codex\Types\CacheMode::AUTO(), //\Codex\Entities\Document::CACHE_AUTO,

            // Whenever a document's last modified time changes, the document's cache is refreshed.
            // It is possible to set this to null making it refresh by checking last modified.
            // Alternatively, you can also set a max duration in minutes.
            // Recommended is to put it on null
            'minutes' => 7,
        ],

        'revision' => [
            'default'              => 'master',
            'allow_php_config'     => false,
            'allowed_config_files' => [ 'revision.yml' ],
        ],

        'document' => [
            'default'    => 'index',
            'extensions' => [ 'md', 'markdown' ],
        ],
    ],

    'revisions' => [],

    'documents' => [
        'title'       => '',
        'subtitle'    => '',
        'description' => '',
    ],
];
