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

namespace Codex\Exceptions;

use Throwable;

class Exception extends \Exception
{
    public static function make($msg, $code = 0, Throwable $previous = null)
    {
        return new static($msg, $code, $previous);
    }

    public static function from(Throwable $previous, $message = null, $code = null)
    {
        return new static(
            $message ?? $previous->getMessage(),
            $code ?? $previous->getCode(),
            $previous
        );
    }
}
