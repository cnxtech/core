<?php

namespace Codex\Concerns;

use Illuminate\Contracts\Events\Dispatcher;

trait HasEvents
{
    public static function getEventNamespace(string $eventName = null)
    {
        $prefix = isset(static::$eventNamePrefix) ? static::$eventNamePrefix : 'codex';
        $name   = snake_case(last(explode('\\', static::class)));
        if ($prefix !== null) {
            $name = "{$prefix}.{$name}";
        }
        if ($eventName !== null) {
            $name = "{$name}.{$eventName}";
        }
        return $name;
    }

    public function fireEvent(string $eventName, ...$params)
    {
        return resolve(Dispatcher::class)->dispatch(static::getEventNamespace($eventName), $params);
    }

    public static function onEvent(string $eventName, callable $callback, bool $wrap = true)
    {
        return resolve(Dispatcher::class)->listen(
            static::getEventNamespace($eventName),
            $wrap ? function (...$params) use ($callback) {
                return app()->call($callback, $params);
            } : $callback
        );
    }
}
