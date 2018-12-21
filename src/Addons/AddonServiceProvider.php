<?php

namespace Codex\Addons;

use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array
     */
    public $providers = [];

    /**
     * An array of the service provider instances.
     *
     * @var array
     */
    public $instances = [];

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    public $listen = [];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    public $subscribe = [];

    public $bindings = [];

    public $singletons = [];

    public $aliases = [];

    public $commands = [];

    public $provides = [];

    public $config = [];

    public $extensions = [];

    /** @var \Codex\Addons\Addon */
    protected $addon;

    /**
     * AddonServiceProvider constructor.
     */
    public function __construct($app, Addon $addon)
    {
        parent::__construct($app);
        $this->addon = $addon;
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return $this->provides;
    }

    public function registerConfig()
    {
        foreach ($this->config as $key => $file) {
            if (is_int($key)) {
                $key  = $file;
                $file = 'config/' . $key;
            }
            $filePath = $this->addon->path($file . '.php');

            $this->mergeConfigFrom($filePath, $key);
            $this->publishes([
                $filePath => config_path(path_get_filename($filePath)),
            ]);
        }
    }
}
