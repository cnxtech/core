<?php

namespace Codex\Documents\Listeners;

use Codex\Addons\Extensions\ExtensionCollection;
use Codex\Documents\Events\ResolvedDocument;
use Codex\Documents\Processors\PostProcessorInterface;
use Codex\Documents\Processors\PreProcessorInterface;
use Codex\Documents\Processors\ProcessorExtension;
use Codex\Documents\Processors\ProcessorInterface;
use Codex\Exceptions\Exception;
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

    /**
     * getSortedExtensionsFor method
     *
     * @param string $interface
     *
     * @return ProcessorExtension[]|\Codex\Addons\Extensions\ExtensionCollection
     */
    protected function getSortedExtensionsFor(string $interface)
    {
        $extensions = $this->extensions->search('codex/core::processor.*');
        $extensions = $extensions->keyBy(function (ProcessorExtension $extension) {
            return $extension->getHandle();
        });
        // only extensions that implement the given interface class
        $filtered = $extensions->filter(function (ProcessorExtension $extension) use ($interface) {
            return in_array($interface, class_implements($extension), true);
        });
        // we might need to remove some of the depends on an extension before sorting for this interface works
        // but we cant remove them directly from the extension instance, as those depends will probably be used
        // for sorting with the other interfaces.
        // so we make a assoc list ($name => $depends) which can be modified without affecting the extension instance
        $list = $filtered->mapWithKeys(function (ProcessorExtension $extension) {
            return [ $extension->getHandle() => $extension->getDependencies() ];
        });
        // remove unknown depends on each item
        $keys = $list->keys()->all();
        $list = $list->map(function ($depends, $name) use ($keys) {
            return array_intersect($depends, $keys);
        });
        // now that we have a valid assoc list, add it all to the sorter and sort
        $sorter = new Sorter();
        $list->each(function ($depends, $name) use ($sorter) {
            $sorter->addItem($name, $depends);
        });
        $sorted = $sorter->sort();
        $sorted = ExtensionCollection::make($sorted)->map(function ($name) use ($extensions) {
            return $extensions->get($name);
        });

        return $sorted;
    }

    protected function getTriggerFor(string $interface)
    {
        if ($interface === PreProcessorInterface::class) {
            return 'pre_process';
        }
        if ($interface === ProcessorInterface::class) {
            return 'process';
        }
        if ($interface === PostProcessorInterface::class) {
            return 'post_process';
        }
        throw Exception::make("Could not get trigger for interface [{$interface}]");
    }

    public function handle(ResolvedDocument $event)
    {
        $document   = $event->getDocument();
        $interfaces = [
            PreProcessorInterface::class,
            ProcessorInterface::class,
            PostProcessorInterface::class,
        ];
        foreach ($interfaces as $interface) {
            /** @var ProcessorExtension[] $sorted */
            $sorted  = $this->getSortedExtensionsFor($interface)->toArray();
            $trigger = $this->getTriggerFor($interface);
            foreach ($sorted as $extension) {
                $document->on($trigger, function () use ($extension, $document, $trigger) {
                    if ( ! $extension->isEnabledForDocument($document)) {
                        return;
                    }
                    $extension->setDocument($document);
                    $methodName = camel_case($trigger);
                    $extension->$methodName($document);
                    $extension->setDocument(null);
                });
            }
        }
    }
}
