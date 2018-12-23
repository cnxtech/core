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

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionType;
use Codex\Contracts\Documents\Document;
use Codex\Documents\Processors\Macros\Macro;

/**
 * This is the class DocTagsFilter.
 *
 * @author         CLI
 * @copyright      Copyright (c) 2015, CLI. All rights reserved
 *
 * @Processor("macros", config="config", after={"parser"})
 */
class MacrosProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    public $defaultConfig = 'codex.processor-defaults.macros';

    /** @var \Codex\Codex */
    public $codex;

    /** @var \Codex\Projects\Project */
    public $project;

    /** @var \Codex\Documents\Document */
    public $document;

    public function getName()
    {
        return 'macros';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $definition->setType(new AttributeDefinitionType('array.scalarPrototype'));
    }

    public function process(Document $document)
    {
        // @formatter:off
        preg_match_all('/<!--\*codex:(.*?)\*-->/', $content = $document->getContent(), $matches);
        $definitions = $this->getAllMacroDefinitions();

        // foreach found macro
        foreach ($matches[0] as $i => $raw) {
            $definition = Macro::extractDefinition($matches[1][$i]);
            if (false === array_key_exists($definition, $definitions)) {
                continue;
            }
            $macro = $this->createMacro($raw, $matches[1][$i]);
//            static::$macros[] = $macro;
            $macro->setHandler($definitions[$macro->definition]);
            $macro->run();
        }
        // @formatter:on
    }

    protected function createMacro($raw, $cleaned)
    {
        $macro           = new Macro($raw, $cleaned);
        $macro->codex    = $this->codex;
        $macro->project  = $this->project;
        $macro->document = $this->document;

        return $macro;
    }

    /**
     * This will get the configured macros. It merges (if defined) the global config, project config and document attributes.
     *
     * Project macros will overide global macros with the same name.
     * Document macros will overide project macros with the same name.
     * Other then that, everything will be merged/inherited.
     *
     * @return array The collected macros as id(used for regex) > handler(the class string name with @ method callsign)
     */
    protected function getAllMacroDefinitions()
    {
        return $this->config();
    }

    public function setDocument($document)
    {
        parent::setDocument($document);
        if ($document === null) {
            $this->revision = $this->project = $this->codex = null;
            return $this;
        }
        $this->revision = $document->getRevision();
        $this->project  = $document->getProject();
        $this->codex    = $document->getCodex();
        return $this;
    }
}
