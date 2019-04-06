<?php

namespace Codex\Config;

use Codex\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;

class LoadConfiguration extends \Illuminate\Foundation\Bootstrap\LoadConfiguration
{

    /**
     * Bootstrap the given application.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->singleton('codex.config', static function (Application $app) {
            return new Repository($app, $app[ 'config' ]);
        });
        $app->alias('codex.config', Repository::class);
        $app->alias('codex.config', RepositoryContract::class);
    }
}
