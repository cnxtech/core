<?php

namespace Codex\Addons\Events;

use Codex\Addons\AddonCollection;

class AddonsHaveRegistered
{
    /** @var \Codex\Addons\AddonCollection */
    protected $addons;

    /**
     * AddonsHaveRegistered constructor.
     *
     * @param \Codex\Addons\AddonCollection $addons
     */
    public function __construct(AddonCollection $addons)
    {
        $this->addons = $addons;
    }

    /**
     * @return \Codex\Addons\AddonCollection
     */
    public function getAddons()
    {
        return $this->addons;
    }


}
