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
use Codex\Attributes\AttributeType;
use Codex\Contracts\Documents\Document;
use Illuminate\Contracts\View\Factory;
use Codex\Attributes\AttributeType as T;

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
//        $definition->setType(new AttributeDefinitionType(T::ARRAY(T::STRING)));
        $buttons        = $definition->child('buttons', AttributeType::MAP);
//        $buttons->child('button', T::ARRAY(T::STRING) );
//        $buttons->child('label', T::STRING);
        $buttonDefaults = $definition->child('button_defaults', T::MAP);
        $definition->child('view', T::STRING)->default('codex::processors.buttons');
    }


    public function process(Document $document)
    {

    }

}
