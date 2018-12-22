<?php

namespace Codex\Http;

use Codex\Codex;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Routing\Router;

class HttpServiceProvider extends RouteServiceProvider
{
    protected $namespace = __NAMESPACE__ . '\Controllers';

    public function map(Router $router, Codex $codex)
    {
        $a = 'a';
        $router
            ->middleware('web')
            ->namespace($this->namespace)
            ->prefix($codex[ 'http.prefix' ])
            ->group(__DIR__ . '/../../routes/web.php');
    }
}
