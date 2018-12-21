<?php

namespace Codex\Addons;

use Codex\Addons\Commands\DisableAddon;
use Codex\Addons\Commands\EnableAddon;
use Codex\Addons\Commands\InstallAddon;
use Codex\Addons\Commands\UninstallAddon;
use Codex\Addons\Events\AddonsHaveRegistered;
use Codex\Addons\Extensions\RegisterExtension;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;

class AddonManager
{
    use DispatchesJobs;

    /** @var \Codex\Addons\AddonFinder */
    protected $finder;

    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    /** @var \Codex\Addons\AddonIntegrator */
    protected $integrator;

    /** @var \Codex\Addons\AddonRegistry */
    protected $registry;

    /** @var \Illuminate\Contracts\Events\Dispatcher */
    protected $dispatcher;

    /** @var \Codex\Addons\AddonCollection */
    protected $addons;

    /**
     * AddonManager constructor.
     *
     * @param \Codex\Addons\AddonFinder               $finder
     * @param \Codex\Addons\AddonIntegrator           $integrator
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(
        AddonFinder $finder,
        AddonIntegrator $integrator,
        AddonRegistry $registry,
        AddonCollection $addons,
        Dispatcher $dispatcher,
        Repository $config)
    {
        $this->finder     = $finder;
        $this->integrator = $integrator;
        $this->registry   = $registry;
        $this->addons     = $addons;
        $this->dispatcher = $dispatcher;
        $this->config     = $config;
    }


    public function register()
    {
        foreach ($this->finder->find() as $path) {
            $addon = $this->integrator->register($path);
            if ( ! $addon) {
                throw new \Exception("Addon path not found [{$path}].");
            }
        }

        $this->dispatcher->dispatch(new AddonsHaveRegistered($this->addons));
    }

    public function install(Addon $addon)
    {
        $this->dispatch(new InstallAddon($addon));
    }

    public function uninstall(Addon $addon)
    {
        $this->dispatch(new UninstallAddon($addon));
    }

    public function enable(Addon $addon)
    {
        $this->dispatch(new EnableAddon($addon));
    }

    public function disable(Addon $addon)
    {
        $this->dispatch(new DisableAddon($addon));
    }
}
