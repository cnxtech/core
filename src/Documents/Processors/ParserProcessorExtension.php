<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;

class ParserProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    protected $defaultConfig = 'codex.processor-defaults.parser';

    protected $after = [ 'attributes', 'cache' ];

    protected $before = [ '*' ];

    public function getName()
    {
        return 'parser';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $parser = $definition->add('markdown', 'dictionary');
        $parser->add('parser', 'string');
        $parser->add('file_types', 'array.scalarPrototype');
        $parser->add('options', 'array.scalarPrototype');
    }

    public function process(Document $document)
    {
        $ext     = $document->getExtension();
        $parser  = $this->getDocumentParser($document);
        $parsed  = $parser->parse($document->getContent(false));
        $content = view($document->getAttribute('view'), [ 'content' => $parsed, 'document' => $document ])->render();
        $document->setContent($content);
    }

    public function getDocumentParser(Document $document)
    {
        $ext    = $document->getExtension();
        $parser = collect($this->config())
            ->filter(function ($item) use ($ext) {
                return \in_array($ext, data_get($item, 'file_types', []), false);
            })->keys()->first();
        if ($parser === null) {
            return null;
        }
        return $this->makeParser($this->config($parser));
    }

    public function makeParser(array $parser)
    {
        $class   = $parser[ 'parser' ];
        $options = data_get($parser, 'options', []);
        /** @var \Codex\Documents\Processors\Parser\ParserInterface $instance */
        $instance = app()->make($class, compact('options'));
        $instance->setOptions($options);
        return $instance;
    }
}
