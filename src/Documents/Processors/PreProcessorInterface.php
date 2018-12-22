<?php


namespace Codex\Documents\Processors;


use Codex\Contracts\Documents\Document;

interface PreProcessorInterface
{
    public function preProcess(Document $document);
}
