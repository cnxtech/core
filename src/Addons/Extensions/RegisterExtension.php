<?php

namespace Codex\Addons\Extensions;

use Codex\Addons\Addon;
use Illuminate\Support\Arr;

class RegisterExtension
{
    /** @var string[] */
    protected $classes;

    protected $addon;

    /**
     * RegisterExtension constructor.
     *
     * @param array|string[]|string $classes
     * @param Addon|null            $addon
     */
    public function __construct($classes, Addon $addon = null)
    {
        $this->classes = Arr::wrap($classes);
        $this->addon   = $addon;
    }

    public function handle(ExtensionCollection $extensions)
    {
        foreach ($this->classes as $class) {
            /** @var \Codex\Addons\Extensions\Extension $extension */
            $extension = app()->make($class);
            if ($this->addon !== null) {
                $extension->setAddon($this->addon);
            }

            $extension->fire('register');

            $extensions->push($extension);

            $extension->fire('registered');
        }
        return $extensions;
    }

}
