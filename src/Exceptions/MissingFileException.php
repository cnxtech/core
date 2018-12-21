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

class MissingFileException extends \RuntimeException
{
    public static function file($filePath)
    {
        return new static("Could not locate [{$filePath}]");
    }
}
