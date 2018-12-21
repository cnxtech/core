<?php

namespace Codex\Addons\Events;

class AddonEvent
{
    /** @var \Codex\Addons\Addon */
    protected $addon;

    /**
     * AddonWasRegistered constructor.
     *
     * @param \Codex\Addons\Addon $addon
     */
    public function __construct($addon)
    {
        $this->addon = $addon;
    }

    /**
     * @return \Codex\Addons\Addon
     */
    public function getAddon()
    {
        return $this->addon;
    }

}
