<?php

namespace Codex\Addons\Commands;

use Codex\Addons\AddonCollection;

class GetAddon
{
    protected $name;

    /**
     * GetAddon constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function handle(AddonCollection $addons)
    {
        return $addons->get($this->name);
    }
}
