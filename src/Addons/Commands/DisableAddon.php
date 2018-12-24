<?php

namespace Codex\Addons\Commands;

use Codex\Addons\Addon;
use Codex\Addons\AddonManager;
use Codex\Addons\AddonRegistry;
use Codex\Addons\Events\AddonWasDisabled;
use Codex\Exceptions\Exception;
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
        if (false === $this->addon->isEnabled()) {
            throw Exception::make("Could not disable addon [{$this->addon->getName()}] because its not enabled");
        }
        $this->addon->fire('disable');

        $registry->setDisabled($this->addon->getName());

        $this->addon->setEnabled(false);

        $this->addon->fire('disabled');

        $dispatcher->dispatch(new AddonWasDisabled($this->addon));
    }

}
