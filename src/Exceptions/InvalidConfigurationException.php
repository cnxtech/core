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

class InvalidConfigurationException extends Exception
{
    /**
     * make method.
     *
     * @param string      $key
     * @param null|string $reason
     *
     * @return static
     */
    public static function reason(string $key, string $reason = null)
    {
        $reason = $reason ?: '';

        return static::make("Invalid configuration for [{$key}]. {$reason}");
    }
}
