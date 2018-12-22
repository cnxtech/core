<?php


namespace Codex\Documents\Processors;


use Codex\Contracts\Documents\Document;

interface PostProcessorInterface
{
    public function postProcess(Document $document);
}
