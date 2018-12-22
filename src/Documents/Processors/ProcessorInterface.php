<?php


namespace Codex\Documents\Processors;


use Codex\Contracts\Documents\Document;

interface ProcessorInterface
{
    public function process(Document $document);
}
