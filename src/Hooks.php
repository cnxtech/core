<?php /** @noinspection ALL */

namespace Codex;

use Illuminate\Routing\Pipeline;
use Illuminate\Support\Arr;


class Hooks
{
    protected static $handlers = [];

    /**
     * @param string|string[]|array $id
     * @param $handler
     */
    public static function register($id, $handler)
    {
        foreach (Arr::wrap($id) as $_id) {
            static::$handlers[ $_id ][] = $handler;
        }
    }

    public static function isRegistered($id)
    {
        return ! empty(static::$handlers[ $id ]);
    }

    public static function clear($name)
    {
        unset(self::$handlers[ $name ]);
    }

    public static function getHandlers($id = null)
    {
        if ($id === null) {
            return static::$handlers;
        }
        if ( ! static::isRegistered($id)) {
            return [];
        }
        return static::$handlers[ $id ];
    }

    public static function run($id, array $args = [], $abortable = false)
    {
        if ($id !== 'hooks.run') {
            static::run('hooks.run', [ $id, $args, $abortable ]);
        }
        foreach (static::getHandlers($id) as $handler) {
//            $retval = app()->call($handler, $args);
            $retval = call_user_func_array($handler, $args);
            if ($abortable === false && $retval !== null && $retval !== true) {
                throw Exceptions\Exception::make("Invalid return from hook [{$id}] handler");
            }
            if ($retval === null) {
                continue;
            }
            if (is_string($retval)) {
                // String returned means error.
                throw Exceptions\Exception::make($retval);
            }
            if ($retval === false) {
                // False was returned. Stop processing, but no error.
                return false;
            }
        }
        return true;
    }

    public static function waterfall($id, $value, array $args = [])
    {
        if ($id !== 'hooks.waterfall') {
            static::run('hooks.waterfall', [ $id, $value, $args ]);
        }
        $pipes = collect(static::getHandlers($id));

        $pipes = $pipes->map(function ($hook) use ($args) {

            return function ($value, $next) use ($hook, $args) {
                if (is_callable($hook)) {
                    return $next($hook($value, ...$args));
                }
                return $next($value);
            };
        })
            ->all();

        return with(new Pipeline())
            ->send($value)
            ->through($pipes)
            ->then(function ($value) {
                return $value;
            });
    }

    public static function waterfall3($id, $value, array $args = [])
    {
        $pipes = collect(static::$handlers[ $id ])
            ->map(function ($hook) {
                return function ($value, ...$args) use ($hook) {
                    if (is_callable($hook)) {
                        $callback   = function ($error, $newValue) use (&$value) {
                            $value = $newValue;
                        };
                        $hookReturn = $hook($value, $callback, ...$args);
                    }
                    return $value;
                };
            })
            ->all();
        foreach ($pipes as $pipe) {
            $value = $pipe($value, $args);
        }
        return $value;
    }

}
