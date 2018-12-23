<?php

namespace Codex\Documents\Processors;

use Codex\Addons\Extensions\ExtensionCollection;
use Laradic\DependencySorter\Sorter;

class ProcessorSorter
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
     * getExtensionsFor method
     *
     * @param string $interface
     *
     * @return \Codex\Documents\Processors\ProcessorExtension[]|\Codex\Addons\Extensions\ExtensionCollection
     */
    protected function getExtensionsFor(string $interface)
    {
        $extensions = $this->extensions->search('codex/core::processor.*');
        $extensions = $extensions->keyBy(function (ProcessorExtension $extension) {
            return $extension->getName();
        });
        // only extensions that implement the given interface class
        return $extensions->filter(function (ProcessorExtension $extension) use ($interface) {
            return in_array($interface, class_implements($extension), true);
        });
    }

    /**
     * getSortedExtensionsFor method
     *
     * @param string $interface
     *
     * @return ProcessorExtension[]|\Codex\Addons\Extensions\ExtensionCollection
     */
    public function getSortedExtensionsFor(string $interface)
    {
        // we might need to remove some of the depends on an extension before sorting for this interface works
        // but we cant remove them directly from the extension instance, as those depends will probably be used
        // for sorting with the other interfaces.
        // so we make a assoc list ($name => $depends) which can be modified without affecting the extension instance
        $extensions = $this->getExtensionsFor($interface);
        $names      = $extensions->keys()->all();
        $afterList  = $extensions->mapWithKeys(function (ProcessorExtension $extension) {
            return [ $extension->getName() => $extension->getAfter() ];
        });
        $beforeList = $extensions->mapWithKeys(function (ProcessorExtension $extension) {
            return [ $extension->getName() => $extension->getBefore() ];
        })->each(function ($before, $name) use ($afterList) {
            foreach ($before as $b) {
                if ($b === '*') {
                    foreach ($afterList as $afterName => $after) {
                        if ($afterName === $name) {
                            continue;
                        }
                        $afterList->pushTo($afterName, $name);
                    }
                } else {
                    $afterList->pushTo($b, $name);
                }
            }
        });


        // remove unknown depends on each item
        $afterList = $afterList->map(function ($depends, $name) use ($names) {
            return array_intersect($depends, $names);
        });
        // remove unknown depends on each item
        $beforeList = $beforeList->map(function ($depends, $name) use ($names) {
            return array_intersect($depends, $names);
        });


        // now that we have a valid assoc list, add it all to the sorter and sort
        $sorter = new Sorter();
        $afterList->each(function ($depends, $name) use ($sorter) {
            $sorter->addItem($name, $depends);
        });
        $sorted = $sorter->sort();
        $sorted = ExtensionCollection::make($sorted)->map(function ($name) use ($extensions) {
            return $extensions->get($name);
        });

        return $sorted;
    }
}
