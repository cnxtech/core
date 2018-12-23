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

class Table
{
    public function responsive($isCloser = false, $num = null, $str = null, $int = null)
    {
        return $isCloser ? '</div>' : '<div class="responsive">';
    }
}
