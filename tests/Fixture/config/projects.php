<?php


$createProjectConfig = function ($key, $displayName, $description, array $meta = []) {
    $meta = array_replace_recursive([
        'icon'     => 'fa-book',
        'color'    => 'deep-orange',
        'author'   => 'Robin Radic',
        'license'  => 'MIT',
        'websites' => [
            'vcs'     => 'https://github.com/codex-project',
            'issues'  => 'https://github.com/codex-project/codex/issues',
            'package' => 'https://packagist.com/codex-project/codex',
        ],
    ], $meta);
    return [
        'key'          => $key,
        'display_name' => $displayName,
        'description'  => $description,
        'meta'         => $meta,

        'processors' => [
            'enabled'    => [ 'attributes', 'parser', 'toc', 'header', 'macros', 'buttons', 'links', 'phpdoc' ],
            'attributes' => [
                'tags' => [
                    [ 'open' => '<!--*', 'close' => '--*>' ], // html, markdown
                    [ 'open' => '---', 'close' => '---' ], // markdown (frontmatter)
                    [ 'open' => '\/\*', 'close' => '\*\/' ], // codex v1 style
                ],
            ],
            'toc'        => [
                'header_link_show' => true,
            ],
        ],
    ];
};

return [
    $createProjectConfig('codex', 'Codex', 'Codex is a file-based documentation system build with Laravel 5.5'),
    $createProjectConfig('blade-extensions', 'Blade Extensions', 'A collection of directives and functionality for Laravel\'s Blade engine', [
        'websites' => [
            'vcs'     => 'https://github.com/robinradic/blade-extensions',
            'issues'  => 'https://github.com/robinradic/blade-extensions/issues',
            'package' => 'https://packagist.com/codex-project/codex',
        ],
    ]),
];
