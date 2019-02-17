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

namespace Codex\Documents\Processors\Links;

use League\Uri\Modifiers\Normalize;
use League\Uri\Http;

class Url extends Http
{
    protected static $supported_schemes = [
        'http' => 80,
        'https' => 443
    ];

    public function toString()
    {
        return $this->__toString();
    }

    public function normalize()
    {
        $normalizer = new Normalize();

        return $normalizer($this);
    }

    public function valid()
    {
        return $this->isValidUri();
    }
}
