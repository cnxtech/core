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
        $app->singleton('codex.config.language', static function (Application $app) {
            $expressionLanguageProvider = new ConfigExpressionLanguageProvider();
            return new ExpressionLanguage(null, [ $expressionLanguageProvider ]);
        });
        $app->singleton('codex.config.processor', static function (Application $app) {
            $processor = new ConfigProcessor($app, $app[ 'codex.config.language' ]);
            $processor->setValues([
                'app'    => $app,
                'config' => $app[ 'config' ],
            ]);
            return $processor;
        });
        $app->singleton('codex.config', static function (Application $app) {
            $config = new Repository($app, $app[ 'config' ], $app[ 'codex.config.processor' ]);
            return $config;
        });
        $app->alias('codex.config', Repository::class);
        $app->alias('codex.config', RepositoryContract::class);
    }
}
