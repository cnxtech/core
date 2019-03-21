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
use Codex\Contracts\Documents\Document;
use Codex\Processors\Toc\Header;
use Illuminate\Contracts\View\Factory;

class ButtonsProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    /** @var \Codex\Codex */
    public $codex;

    protected $defaultConfig = 'codex.processor-defaults.buttons';

    protected $after = [];

    /** @var \Illuminate\Contracts\View\Factory */
    protected $view;

    protected $slugs = [];

    protected $nodes = [];

    /**
     * TocFilter constructor.
     *
     * @param $view
     */
    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    public function getName()
    {
        return 'buttons';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
//        $definition->setType(new AttributeDefinitionType('array.scalarPrototype'));
        $buttons        = $definition->add('buttons', 'dictionaryPrototype', '[Assoc]');
//        $buttons->add('button', 'array.scalarPrototype', 'Assoc');
//        $buttons->add('label', 'string');
        $buttonDefaults = $definition->add('button_defaults', 'dictionary', 'Assoc', []);
        $definition->add('view', 'string')->setDefault('codex::processors.buttons');
    }


    public function process(Document $document)
    {

    }

}
