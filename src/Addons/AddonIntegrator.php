<?php

namespace Codex\Addons;

use Codex\Addons\Events\AddonWasRegistered;
use Codex\Addons\Extensions\ExtensionCollection;
use Codex\Addons\Extensions\RegisterExtension;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;

class AddonIntegrator
{
    use DispatchesJobs;

    /** @var \Codex\Addons\AddonCollection */
    protected $collection;

    /** @var \Illuminate\Contracts\Events\Dispatcher */
    protected $events;

    /** @var \Codex\Addons\AddonRegistry */
    protected $registry;

    /** @var \Codex\Addons\Extensions\ExtensionCollection */
    protected $extensions;

    /** @var \Codex\Addons\AddonProvider */
    protected $addonProvider;

    /**
     * AddonIntegrator constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \Codex\Addons\AddonCollection             $collection
     * @param \Illuminate\Contracts\Events\Dispatcher   $events
     * @param \Codex\Addons\AddonRegistry               $registry
     */
    public function __construct(
        AddonCollection $collection,
        ExtensionCollection $extensions,
        AddonRegistry $registry,
        AddonProvider $addonProvider,
        Dispatcher $events
    )
    {
        $this->collection = $collection;
        $this->events     = $events;
        $this->registry   = $registry;
        $this->extensions = $extensions;
        $this->addonProvider = $addonProvider;
    }


    public function register($path)
    {
        if ( ! is_dir($path)) {
            return null;
        }

        $composer = $this->getComposerData($path);
        list($vendor, $slug) = explode('/', $composer[ 'name' ]);

        $class = studly_case($vendor) . '\\' . studly_case($slug) . '\\' . studly_case($slug) . 'Addon';

        /** @var \Codex\Addons\Addon $addon */
        $addon = app($class);
        $addon
            ->setVendor($vendor)
            ->setSlug($slug)
            ->setPath($path);

        $name = $addon->getName();
        if ( ! $this->registry->exists($name)) {
            $this->registry->create($name);
        }

        $addon
            ->setEnabled($enabled = $this->registry->isEnabled($name))
            ->setInstalled($installed = $this->registry->isInstalled($name));

        $this->collection->put($addon->getName(), $addon);

        $this->addonProvider->register($addon);

        $this->events->dispatch(new AddonWasRegistered($addon));

        return $addon;
    }

    protected function getComposerData($path)
    {
        if ( ! file_exists($path . '/composer.json')) {
            throw new \Exception("Composer file not found at {$path}/composer.json");
        }

        if ( ! $composer = json_decode(file_get_contents($path . '/composer.json'), true)) {
            throw new \Exception("A JSON syntax error was encountered in {$path}/composer.json");
        }

        return $composer;
    }
}
