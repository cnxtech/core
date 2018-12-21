<?php
/**
 * Part of the Codex Project packages.
 *
 * License and copyright information bundled with this package in the LICENSE file.
 *
 * @author    Robin Radic
 * @copyright Copyright 2017 (c) Codex Project
 * @license   http://codex-project.ninja/license The MIT License
 */

return [
    'attributes' => [
        'tags'           => [
            [ 'open' => '<!--*', 'close' => '--*>' ], // html, markdown
            [ 'open' => '---', 'close' => '---' ], // markdown (frontmatter)
        ],
        'remove_tags'    => true,
        'add_extra_data' => true,
    ],
    'buttons'    => [
        'buttons'                   => [
//            'button-id' => [
//                'text'   => 'Haai',
//                'icon'   => 'fa fa-github',
//                'href'   => 'http://goto.com/this',
//                'target' => '_blank',
//            ],
        ],
        'default_button_attributes' => [
            'color'  => 'secondary',
            'href'   => 'javascript:;',
            'class'  => [],
            'target' => '_blank',
        ],
        'view'                      => 'codex::processors.buttons',

    ],
    'header'     => [
        'view'                 => 'codex::processors.header',
        'remove_from_document' => true,
        'remove_regex'         => '/<h1>(.*?)<\/h1>/',
    ],
    'links'      => [
        'prefix'  => 'codex',
        'actions' => [
        ],
    ],
    'macro'      => [
        'macros' => [
            'table:responsive'        => 'Codex\Processors\Macros\Table@responsive',
            'layout:row'              => 'Codex\Processors\Macros\Layout@row',
            'layout:column'           => 'Codex\Processors\Macros\Layout@column',
            'general:hide'            => 'Codex\Processors\Macros\General@hide',
            'attribute:print'         => 'Codex\Processors\Macros\Attribute@printValue',
            'phpdoc:method:signature' => 'Codex\Addon\Phpdoc\PhpdocMacros@methodSignature',
            'phpdoc:method'           => 'Codex\Addon\Phpdoc\PhpdocMacros@method',
            'phpdoc:entity'           => 'Codex\Addon\Phpdoc\PhpdocMacros@entity',
            'phpdoc:list:method'      => 'Codex\Addon\Phpdoc\PhpdocMacros@listMethod',
            'phpdoc:list:property'    => 'Codex\Addon\Phpdoc\PhpdocMacros@listProperty',
        ],
    ],
    'parser'     => [
        'parser'   => 'Codex\Processors\Parser\MarkdownParser', // the parser with name 'markdown'
        'markdown' => [ // refers to parser name
            'renderer' => 'Codex\Processors\Parser\Markdown\CodexMarkdownRenderer',
            'html5'    => true,
            'table'    => [
                'class' => 'table stack',
            ],
            'code'     => [
                'line_numbers' => true,
                'command_line' => true,
                'loader'       => false,
            ]
            // additional custom config possible for the renderer
        ],
        'rst'      => [ // refers to parser name
//            'renderer' => 'Codex\Processors\Parser\Rst\RstRenderer',
        ],

    ],
    'toc'        => [
        'disable'           => [ 1 ],
        'regex'             => '/<h(\d)>([\w\W]*?)<\/h\d>/',
        'list_class'        => 'c-toc-list',
        'header_link_class' => 'c-toc-header-link',
        'header_link_show'  => false,
        'header_link_text'  => '#',
        'minimum_nodes'     => 2,
        'view'              => 'codex::processors.toc',
    ],
];


