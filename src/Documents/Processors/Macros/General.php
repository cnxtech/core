<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author    Robin Radic
 * @license   https://codex-project.mit-license.org MIT License
 */

namespace Codex\Documents\Processors\Macros;

class General
{
    public function hide($isCloser = false)
    {
        return $isCloser ? '</div>' : '<div style="display:none">';
    }
}
