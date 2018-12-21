<?php

namespace Codex\Documents\Listeners;

use Codex\Addons\Extensions\ExtensionCollection;
use Codex\Documents\Events\ResolvedDocument;
use Laradic\DependencySorter\Sorter;

class ProcessDocument
{
    /** @var \Codex\Addons\Extensions\ExtensionCollection */
    protected $extensions;

    /**
     * ProcessDocument constructor.
     *
     * @param \Codex\Addons\Extensions\ExtensionCollection $extensions
     */
    public function __construct(ExtensionCollection $extensions)
    {
        $this->extensions = $extensions;
    }

    public function handle(ResolvedDocument $event)
    {
        $document   = $event->getDocument();
        $extensions = $this->extensions->search('codex/core::processor.*');
        $sorter     = new Sorter();
        $sorter->add($extensions->all());
        foreach ($sorter->sort() as $name) {
            /** @var \Codex\Documents\Processors\ProcessorExtension $extension */
            $extension = $this->extensions->find('codex/core::processor.' . $name);
            if ($extension->isEnabledForDocument($document)) {
                $on = ($extension->isPre() ? 'pre' : 'post') . '_process';
                $document->on($on, function () use ($extension, $document) {
                    $extension->handle($document);
                });
            }
        }
    }
}
