<?php

namespace Codex\Support;

use Barryvdh\Debugbar\LaravelDebugbar;

class DB
{
    static protected function run($name, array $args = [])
    {
        if ( ! app()->bound('debugbar')) {
            return;
        }
        /** @var $bar LaravelDebugbar */
        $bar = app()->make('debugbar');

        if ( ! $bar->isEnabled() || ! method_exists($bar, $name)) {
            return;
        }

        $bar->$name(...$args);

    }

    static public function startMeasure($name, $label = null)
    {
        static::run(__FUNCTION__, func_get_args());
    }

    static public function stopMeasure($name)
    {
        static::run(__FUNCTION__, func_get_args());
    }
}
