<?php

namespace Codex\Addons\Extensions;

use Codex\Addons\Addon;
use Illuminate\Support\Arr;

class RegisterExtension
{
    /** @var string[] */
    protected $class;

    protected $addon;

    /**
     * RegisterExtension constructor.
     *
     * @param array|string[]|string $class
     * @param Addon|null            $addon
     */
    public function __construct($class, Addon $addon = null)
    {
        $this->class = Arr::wrap($class);
        $this->addon = $addon;
    }

    public function handle(ExtensionCollection $extensions)
    {
        foreach ($this->class as $class) {
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
