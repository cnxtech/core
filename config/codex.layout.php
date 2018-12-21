<?php

return [
    'header' => [
        'color'           => 'blue-grey-9',
        'show'            => false,
        'static'          => true,
        'class'           => [ 'c-header' => true ],
        'style'           => [],
        'menu'            => [],
        'title'           => config('codex.display_name', config('app.name')),
        'revealOnScroll'  => true,
        'showTitle'       => true,
        'showLeftToggle'  => true,
        'showRightToggle' => false,
    ],
    'left'   => [
        'show'         => true,
        'static'       => false,
        'below_header' => true,
        'aboveFooter'  => true,
        'class'        => [ 'c-left' => true ],
        'style'        => [],
        'menu'         => [],
    ],
    'right'  => [
        'belowHeader' => true,
        'aboveFooter' => true,
        'class'       => [ 'c-right' => true ], //
        'show'        => false, //
        'static'      => true, //
        'style'       => [], //
        'menu'        => [], //
    ],
    'page'   => [
        'class' => [ 'c-page' => true ],
        'style' => [ 'padding' => '24px', 'background' => '#FFFFFF' ],
    ],
    'footer' => [
        'color'  => 'blue-grey-9',
        'class'  => [ 'c-footer' => true ],
        'show'   => true,
        'static' => false,
        'style'  => [],
        'menu'   => [],
    ],
];
