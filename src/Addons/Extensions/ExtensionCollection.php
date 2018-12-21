<?php

namespace Codex\Addons\Extensions;

use Codex\Addons\Addon;
use Illuminate\Support\Collection;

class ExtensionCollection extends Collection
{


    /**
     * Search for and return matching extensions.
     *
     * @param  mixed $pattern
     * @param  bool  $strict
     *
     * @return ExtensionCollection|\Codex\Addons\Extensions\Extension[]
     */
    public function search($pattern, $strict = false)
    {
        $matches = [];

        foreach ($this->items as $item) {

            /* @var Extension $item */
            if (str_is($pattern, $item->getProvides())) {
                $matches[] = $item;
            }
        }

        return self::make($matches);
    }

    /**
     * Get an extension by it's reference.
     *
     * Example: extension.users::authenticator.default
     *
     * @param  mixed $key
     *
     * @return null|Extension
     */
    public function find($key)
    {
        foreach ($this->items as $item) {

            /* @var Extension $item */
            if ($item->getProvides() == $key) {
                return $item;
            }
        }

        return null;
    }

    /**
     * addon method
     *
     * @param \Codex\Addons\Addon $addon
     *
     * @return \Codex\Addons\Extensions\ExtensionCollection|\Codex\Addons\Extensions\Extension[]
     */
    public function addon(Addon $addon)
    {
        $matches = [];
        foreach ($this->items as $item) {
            $_addon = $item->getAddon();
            if ($_addon instanceof Addon && $_addon->getName() === $addon->getName()) {
                $matches[] = $item;
            }
        }
        return self::make($matches);
    }
}
