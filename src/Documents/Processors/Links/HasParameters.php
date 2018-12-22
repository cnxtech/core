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

trait HasParameters
{
    protected $parameters = [];

    /**
     * hasParameters method.
     *
     * @return bool
     */
    public function hasParameters(): bool
    {
        return \count($this->parameters) > 0;
    }

    /**
     * param method.
     *
     * @param      $i
     * @param null $default
     *
     * @return mixed
     */
    public function param(int $i, $default = null)
    {
        return $this->hasParameter($i) ? $this->parameters[$i] : $default;
    }

    /**
     * hasParameter method.
     *
     * @param $i
     *
     * @return bool
     */
    public function hasParameter(int $i)
    {
        return isset($this->parameters[$i]);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function countParameters(): int
    {
        return \count($this->parameters);
    }
}
