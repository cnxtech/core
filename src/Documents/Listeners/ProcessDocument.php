<?php

namespace Codex\Documents\Listeners;

use Codex\Addons\Extensions\ExtensionCollection;
use Codex\Documents\Events\ResolvedDocument;
use Codex\Documents\Processors\PostProcessorInterface;
use Codex\Documents\Processors\PreProcessorInterface;
use Codex\Documents\Processors\ProcessorInterface;
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

            if ($extension instanceof PreProcessorInterface) {
                $document->on('pre_process', function () use ($extension, $document) {
                    if ( ! $extension->isEnabledForDocument($document)) {
                        return;
                    }
                    $extension->setDocument($document);
                    $extension->preProcess($document);
                    $extension->setDocument(null);
                });
            }

            if ($extension instanceof ProcessorInterface) {
                $document->on('process', function () use ($extension, $document) {
                    if ( ! $extension->isEnabledForDocument($document)) {
                        return;
                    }
                    $extension->setDocument($document);
                    $extension->process($document);
                    $extension->setDocument(null);
                });
            }

            if ($extension instanceof PostProcessorInterface) {
                $document->on('post_process', function () use ($extension, $document) {
                    if ( ! $extension->isEnabledForDocument($document)) {
                        return;
                    }
                    $extension->setDocument($document);
                    $extension->postProcess($document);
                    $extension->setDocument(null);
                });
            }
        }
    }
}
