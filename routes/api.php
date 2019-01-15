<?php


$controller = 'ApiController@query';
$route      = codex()->attr('http.api_prefix', 'api');
$name       = 'codex.api';

if (config('lighthouse.route_enable_get', false)) {
    Route::get($route)
        ->name($name)
        ->uses($controller);
}

Route::post($route)
    ->name($name)
    ->uses($controller);
