<?php

namespace Codex\Http;

use Closure;

class DebugbarCollectionLoggerMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next)
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

//        $collection = debugbar()->collect();
//        $ctl        = data_get($collection, 'route.controller');
//        if ($ctl === 'Codex\Http\Controllers\DocumentController@getDocument' || $ctl === 'Codex\Http\Controllers\ApiController@query') {
//            app()->make('codex.log')->addRecord(100, 'debugbar collection', array_except($collection, [ 'request', 'auth', 'session']));//array_only($collection,['php','memory','route']));
//        }
        return $response;
    }
}
