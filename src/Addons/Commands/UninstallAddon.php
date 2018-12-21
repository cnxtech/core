<?php

namespace Codex\Addons\Commands;

use Codex\Addons\Addon;
use Codex\Addons\AddonManager;
use Codex\Addons\AddonRegistry;
use Codex\Addons\Events\AddonWasUninstalled;
use Illuminate\Contracts\Events\Dispatcher;

class UninstallAddon
{
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
        $this->addon->fire('uninstall');

        $registry->setInstalled($this->addon->getName(), false);

        $this->addon->setInstalled(false);

        $this->addon->fire('uninstalled');

        $dispatcher->dispatch(new AddonWasUninstalled($this->addon));
    }

}
