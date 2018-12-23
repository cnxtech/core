<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

namespace Codex\Documents\Processors\Macros;

class Layout
{
    public function row($isCloser = false)
    {
        return $isCloser ? '</div>' : '<div class="row">';
    }

    public function column($isCloser = false, $breakpoint = 'sm', $width = '12')
    {
        $class = "col-{$breakpoint}-{$width}";

        return $isCloser ? '</div>' : "<div class=\"{$class}\">";
    }
}
