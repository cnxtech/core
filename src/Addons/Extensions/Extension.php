<?php

namespace Codex\Addons\Extensions;

use Codex\Concerns\HasCallbacks;

class Extension
{
    use HasCallbacks;

    protected $addon = null;

    protected $provides;

    /**
     * @return mixed
     */
    public function getAddon()
    {
        return $this->addon;
    }

    /**
     * Set the addon value
     *
     * @param mixed $addon
     *
     * @return Extension
     */
    public function setAddon($addon)
    {
        $this->addon = $addon;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProvides()
    {
        return $this->provides;
    }

    /**
     * Set the provides value
     *
     * @param mixed $provides
     *
     * @return Extension
     */
    public function setProvides($provides)
    {
        $this->provides = $provides;
        return $this;
    }



}
