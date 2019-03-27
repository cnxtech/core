<?php

namespace Codex\Config;

use Codex\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;

class LoadConfiguration extends \Illuminate\Foundation\Bootstrap\LoadConfiguration
{

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        /** @var \Illuminate\Contracts\Config\Repository $laravelConfig */
        $laravelConfig = $app->make('config');
        $app->instance('codex.config', $config = new Repository($laravelConfig));
        $app->alias('codex.config', Repository::class);
        $app->alias('codex.config', RepositoryContract::class);
    }
}
