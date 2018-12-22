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

namespace Codex\Documents\Commands;


use Codex\Contracts\Documents\Document;

class AssignClassDocumentProperties
{
    protected $instance;

    /** @var \Codex\Contracts\Documents\Document  */
    protected $document;

    public function __construct(Document $document, $target)
    {
        $this->document = $document;
        $this->instance = $target;
    }

    public function handle()
    {
        $document = $this->document;
        $instance = $this->instance;
        if (property_exists($instance, 'container')) {
            $instance->container = $document->getContainer();
        }
        if (property_exists($instance, 'codex')) {
            $instance->codex = $document->getCodex();
        }
        if (property_exists($instance, 'project')) {
            $instance->project = $document->getProject();
        }
        if (property_exists($instance, 'revision')) {
            $instance->revision = $document->getRevision();
        }
        if (property_exists($instance, 'document')) {
            $instance->document = $document;
        }

        if (method_exists($instance, 'afterAssignProperties')) {
            $instance->afterAssignProperties();
        }
    }
}
