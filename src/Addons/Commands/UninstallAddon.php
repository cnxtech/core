<?php

namespace Codex\Addons\Commands;

use Codex\Addons\Addon;
use Codex\Addons\AddonManager;
use Codex\Addons\AddonRegistry;
use Codex\Addons\Events\AddonWasUninstalled;
use Codex\Hooks;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UninstallAddon
{
    use DispatchesJobs;

    /** @var \Codex\Addons\Addon */
    protected $addon;

    /**
     * InstallAddon constructor.
     *
     * @param \Codex\Addons\Addon $addon
     */
    public function __construct(Addon $addon)
    {
        $this->addon = $addon;
    }

    public function handle(
        AddonRegistry $registry,
        AddonManager $manager,
        Dispatcher $dispatcher
    )
    {
        if ($this->addon->isEnabled()) {
            $this->dispatch(new DisableAddon($this->addon));
        }

        if (Hooks::run('addon.uninstall', [ $this->addon ], true)) {
            $this->addon->fire('uninstall');

            $registry->setUninstalled($this->addon->getName());

            $this->addon->setInstalled(false);

            $this->addon->fire('uninstalled');

            $dispatcher->dispatch(new AddonWasUninstalled($this->addon));
        }
    }

}
