<?php

namespace Codex\Addons\Commands;

use Codex\Addons\Addon;
use Codex\Addons\AddonManager;
use Codex\Addons\AddonRegistry;
use Codex\Addons\Events\AddonWasEnabled;
use Codex\Hooks;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;

class EnableAddon
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
        if ( ! $this->addon->isInstalled()) {
            $this->dispatch(new InstallAddon($this->addon));
        }

        if (Hooks::run('addon.enable', [ $this->addon ], true)) {
            $this->addon->fire('enable');

            $registry->setEnabled($this->addon->getName());

            $this->addon->setEnabled(true);

            $this->addon->fire('enabled');

            $dispatcher->dispatch(new AddonWasEnabled($this->addon));
        }
    }

}
