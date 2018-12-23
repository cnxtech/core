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

/**
 * This is the class Attribute.
 *
 * @author         Robin Radic
 */
class Attribute
{
    /** @var \Codex\Document */
    public $document;

    /** @var \Codex\Entities\Project */
    public $project;

    public function printValue($isCloser = false, $key, $default = null)
    {
        return $this->document->attr($key, $default);
    }
}
