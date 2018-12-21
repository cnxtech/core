<?php

namespace Codex\Addons\Commands;

use Codex\Addons\Addon;
use Codex\Addons\AddonManager;
use Codex\Addons\AddonRegistry;
use Codex\Addons\Events\AddonWasDisabled;
use Illuminate\Contracts\Events\Dispatcher;

class DisableAddon
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
        $this->addon->fire('disable');

        $registry->setEnabled($this->addon->getName(), false);

        $this->addon->setEnabled(false);

        $this->addon->fire('disabled');

        $dispatcher->dispatch(new AddonWasDisabled($this->addon));
    }

}
