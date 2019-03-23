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
        'tags' => [
            [ 'open' => '<!--*', 'close' => '--*>' ], // html, markdown
            [ 'open' => '---', 'close' => '---' ], // markdown (frontmatter)
        ],
    ],
    'links'      => [
        'prefix'      => 'codex',// the prefix for actions, ex:  [Click me](#codex:project[my-awesome-project])
        'replace_tag' => false,  // a string like 'div' will replace <a> to <div>. set to false to not replace
        'actions'     => [
            'project'  => \Codex\Documents\Processors\Links\CodexLinks::class . '@project',
            'revision' => \Codex\Documents\Processors\Links\CodexLinks::class . '@revision',
            'document' => \Codex\Documents\Processors\Links\CodexLinks::class . '@document',
        ],
    ],
    'macros'     => [
        'attribute:print' => 'Codex\Documents\Processors\Macros\Attribute@printValue',
        'hide'            => 'Codex\Documents\Processors\Macros\General@hide',
        'gist'            => 'Codex\Documents\Processors\Macros\Components@gist',
        'scrollbar'       => 'Codex\Documents\Processors\Macros\Components@scrollbar',
        'tabs'            => 'Codex\Documents\Processors\Macros\Components@tabs',
        'tab'             => 'Codex\Documents\Processors\Macros\Components@tab',
        'row'             => 'Codex\Documents\Processors\Macros\Components@row',
        'col'             => 'Codex\Documents\Processors\Macros\Components@col',
    ],
    'parser'     => [
        'markdown' => [
            'parser'     => 'Codex\Documents\Processors\Parser\CommonMarkParser',
            'file_types' => [ 'md', 'markdown' ],
            'options'    => [

                'element_attributes' => [
                    'table'            => [
                        'class' => 'table hover',
                    ],
                    'c-code-highlight' => [
                        'props' => [
                            'withLineNumbers' => true,
                            'withCommandLine' => true,
                            'withLoader'      => false,
                        ],
                    ],
                ],
            ],
        ],
        'rst'      => [], // refers to parser name

    ],
    'toc'        => [
        'disable'          => [ 1 ],
        'regex'            => '/<h(\d)>([\w\W]*?)<\/h\d>/',
        'header_link_show' => false,
        'header_link_text' => '#',
        'minimum_nodes'    => 2,
        'view'             => 'codex::react.processors.toc',//'codex::processors.toc',
        'header_view'      => 'codex::react.processors.toc-header',//'codex::processors.toc-header',
    ],
    'buttons'    => [
        'buttons'         => [
//            'button-id' => [
//                'text'   => 'Haai',
//                'icon'   => 'fa fa-github',
//                'href'   => 'http://goto.com/this',
//                'target' => '_blank',
//            ],
        ],
        'button_defaults' => [
            'color'  => 'secondary',
            'href'   => 'javascript:;',
            'class'  => [],
            'target' => '_blank',
        ],
        'view'            => 'codex::processors.buttons',

    ],
    'header'     => [
        'view'                 => 'codex::processors.header',
        'remove_from_document' => true,
        'remove_regex'         => '/<h1>(.*?)<\/h1>/',
    ],
];


