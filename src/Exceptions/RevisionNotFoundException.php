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

namespace Codex\Exceptions;

/**
 * This is the class RevisionNotFoundException.
 *
 * @author  Robin Radic
 *
 * @deprecated Use NotFoundException
 * @see NotFoundException
 */
class RevisionNotFoundException extends NotFoundException
{
    public static function make($key)
    {
        return static::revision($key);
    }
}
