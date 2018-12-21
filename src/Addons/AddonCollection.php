<?php

namespace Codex\Addons;

use Illuminate\Support\Collection;

class AddonCollection extends Collection
{

    /**
     * enabled method
     *
     * @return AddonCollection
     */
    public function installed()
    {
        return $this->filter(function (Addon $addon) {
            return $addon->isInstalled();
        });
    }

    /**
     * enabled method
     *
     * @return AddonCollection
     */
    public function uninstalled()
    {
        return $this->filter(function (Addon $addon) {
            return ! $addon->isInstalled();
        });
    }

    /**
     * enabled method
     *
     * @return AddonCollection
     */
    public function enabled()
    {
        return $this->filter(function (Addon $addon) {
            return $addon->isEnabled();
        });
    }

    /**
     * enabled method
     *
     * @return AddonCollection
     */
    public function disabled()
    {
        return $this->filter(function (Addon $addon) {
            return ! $addon->isEnabled();
        });
    }
}
