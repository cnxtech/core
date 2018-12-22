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

class Modifier
{
    use HasParameters;

    protected $name;

    /**
     * Modifier constructor.
     *
     * @param       $name
     * @param array $parameters
     */
    public function __construct($name, array $parameters = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }
}
