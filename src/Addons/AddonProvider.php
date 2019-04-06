<?php

namespace Codex\Addons;

use Closure;
use Codex\Addons\Extensions\RegisterExtension;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Router;

class AddonProvider
{
    use DispatchesJobs;

    /**  @var array */
    protected $providers = [];

    /**
     * An array of the service provider instances.
     *
     * @var array
     */
    protected $instances = [];

    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $application;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    /** @var \Illuminate\Contracts\Events\Dispatcher */
    protected $events;

    /**
     * AddonProvider constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $application
     * @param \Illuminate\Routing\Router                   $router
     * @param \Illuminate\Contracts\Events\Dispatcher      $events
     */
    public function __construct(
        Application $application,
        Router $router,
        Dispatcher $events
    )
    {
        $this->application = $application;
        $this->router      = $router;
        $this->events      = $events;
    }


    public function register(Addon $addon)
    {
        if ( ! $addon->isEnabled()) {
            return $this;
        }

        $provider = $addon->getServiceProvider();

        if ( ! class_exists($provider)) {
            return $this;
        }

        /** @var \Codex\Addons\AddonServiceProvider $provider */
        $this->providers[] = $provider = $addon->newServiceProvider();
        $this->registerConfig($provider);
        $this->application->register($provider);
        $this->mapConfig($provider);
        $this->bindAliases($provider);
        $this->registerEvents($provider);
        $this->registerCommands($provider);
        $this->dispatch(new RegisterExtension($provider->extensions, $addon));

        $this->registerProviders($provider);

        $boot = function () use ($provider) {
            if (method_exists($provider, 'attributes')) {
                $this->application->call([ $provider, 'attributes' ]);
            }
        };
        if ($this->application->hasBeenBootstrapped()) {
            $boot();
        } else {
            $this->application->booting($boot);
        }
        return $provider;
    }

    protected function registerConfig(AddonServiceProvider $provider)
    {
        $provider->registerConfig();
    }

    protected function mapConfig(AddonServiceProvider $provider)
    {
        $config = $this->application[ 'config' ];
        foreach ($provider->mapConfig as $from => $to) {
            $fromValue = $config->get($from, []);
            if ($fromValue instanceof Closure) {
                $fromValue = app()->call($to);
            }
            if (is_array($fromValue)) {
                foreach ($fromValue as $key => $value) {
                    $config->set($to . '.' . $key, $value);
                }
            } else {
                $config->set($to, $fromValue);
            }
        }
    }

    protected function registerProviders(AddonServiceProvider $provider)
    {
        $provides        = $provider->provides;
        $this->instances = [];
        foreach ($provider->providers as $_provider) {
            $instance          = $this->application->register($_provider);
            $this->instances[] = $instance;
            $provides          = array_unique(array_merge($provides, $instance->provides()));
        }
        $provider->provides = $provides;
    }

    /**
     * Register the addon commands.
     *
     * @param AddonServiceProvider $provider
     */
    protected function registerCommands(AddonServiceProvider $provider)
    {
        if ( ! $provider->commands) {
            return;
        }
        // To register the commands with Artisan, we will grab each of the arguments
        // passed into the method and listen for Artisan "start" event which will
        // give us the Artisan console instance which we will give commands to.
        $this->events->listen(ArtisanStarting::class, function (ArtisanStarting $event) use ($provider) {
            $event->artisan->resolveCommands($provider->commands);
        });
    }

    /**
     * Bind class aliases.
     *
     * @param AddonServiceProvider $provider
     */
    protected function bindAliases(AddonServiceProvider $provider)
    {
        if ($aliases = $provider->aliases) {
            AliasLoader::getInstance($aliases)->register();
        }
    }

    protected function registerEvents(AddonServiceProvider $provider)
    {
        foreach ($provider->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->events->listen($event, $listener);
            }
        }

        foreach ($provider->subscribe as $subscriber) {
            $this->events->subscribe($subscriber);
        }
    }
}
