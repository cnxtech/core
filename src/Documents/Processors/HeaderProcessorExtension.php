<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Codex\Attributes\AttributeType as T;

class HeaderProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    protected $defaultConfig = 'codex.processor-defaults.header';

    protected $after = [ 'parser', 'toc' ];

    public function getName()
    {
        return 'header';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $definition->child('view', T::STRING);                 // => 'codex::processors.header',
        $definition->child('remove_from_document', T::BOOL); // => true,
        $definition->child('remove_regex', T::STRING);         // => '/<h1>(.*?)<\/h1>/',// TODO: Implement defineConfigAttributes() method.
    }

    public function process(Document $document)
    {
        if ($this->config('remove_from_document')) {
            $this->remove($document);
        }

        $html = view($this->config('view'), compact('document'))->render();
        $document->setContent($html . $document->getContent());
    }

    protected function remove(Document $d)
    {
        if (false !== $d->attr('title', false)) {
            $d->setContent(preg_replace($this->config('remove_regex'), '', $d->getContent(), 1));
        }
    }
}
