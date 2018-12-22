<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;

class ParserProcessorExtension extends ProcessorExtension
{
    protected $defaultConfig = 'codex.processor-defaults.parser';

    protected $pre = true;

    protected $depends = [ 'attributes' ];

    public function getName()
    {
        return 'parser';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
//        AttributeDefinitionFactory::attribute()

        $parser = $definition->add('markdown', 'dictionary');
        $parser->add('parser', 'string');
        $parser->add('file_types', 'array.scalarPrototype');
        $parser->add('options', 'array.scalarPrototype');
    }

    public function process(Document $document)
    {
        $ext    = $document->getExtension();
        $parser = $this->getDocumentParser($document);
        $parsed = $parser->parse($document->getContent());
        $document->setContent($parsed);
    }

    protected function getDocumentParser(Document $document)
    {
        $ext    = $document->getExtension();
        $parser = collect($this->config())
            ->filter(function ($item) use ($ext) {
                return \in_array($ext, data_get($item, 'file_types', []), false);
            })->first();
        if ($parser === null) {
            return null;
        }
        $class   = $parser[ 'parser' ];
        $options = data_get($parser, 'options', []);
        /** @var \Codex\Documents\Processors\Parser\ParserInterface $instance */
        $instance = app()->make($class, compact('options'));
        $instance->setOptions($options);
        return $instance;
    }
}
