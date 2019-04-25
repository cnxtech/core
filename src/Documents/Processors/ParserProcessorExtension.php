<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeType as T;
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
        $definition->type(T::MAP());
//        $parser = $definition->child('markdown', T::ARRAY(T::MAP));
//        $parser->child('parser', T::STRING);
//        $parser->child('file_types', T::ARRAY(T::STRING));
//        $parser->child('options', T::ARRAY(T::MAP));

//        $definition->child('parser', T::STRING);
//        $definition->child('file_types', T::ARRAY(T::STRING));
//        $definition->child('options', T::MAP);
    }

    public function process(Document $document)
    {
        $ext     = $document->getExtension();
        $parser  = $this->getDocumentParser($document);
        $parsed  = $parser->parse($document->getContent());
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
