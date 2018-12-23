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

use Codex\Addons\Annotations as CA;

/**
 * This is the class PageList.
 *
 * @author         Robin Radic
 * @CA\Macro("pagelist")
 */
class PageList
{
    /** @var \Codex\Document */
    public $document;

    /** @var \Codex\Project */
    public $project;

    public function out($isCloser = false, $path, $exclude)
    {
    }
}
