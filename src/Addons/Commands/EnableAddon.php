<?php

namespace Codex\Addons\Commands;

use Codex\Addons\Addon;
use Codex\Addons\AddonManager;
use Codex\Addons\AddonRegistry;
use Codex\Addons\Events\AddonWasEnabled;
use Codex\Addons\Events\AddonWasInstalled;
use Illuminate\Contracts\Events\Dispatcher;

class EnableAddon
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
        $this->addon->fire('enable');

        $registry->setEnabled($this->addon->getName(), true);

        $this->addon->setEnabled(true);

        $this->addon->fire('enabled');

        $dispatcher->dispatch(new AddonWasEnabled($this->addon));
    }

}
