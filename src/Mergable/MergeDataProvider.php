<?php

namespace Codex\Mergable;

use Codex\Contracts\Mergable\MergableDataProviderInterface;
use Illuminate\Support\Arr;

class MergeDataProvider implements MergableDataProviderInterface
{
    protected $fn;

    public static function make($data)
    {
        $provider = new static();
        if (Arr::accessible($data)) {
            $provider->fn = function ($key, $default = null) use ($data) {
                return data_get($data, $key, $default);
            };
        } elseif (method_exists($data, 'get')) {
            $provider->fn = function ($key, $default = null) use ($data) {
                return $data->get($key, $default);
            };
        } elseif (is_callable($data)) {
            $provider->fn = $data;
        }
        return $provider;
    }

    /**
     * get method
     *
     * @param string $key
     *
     * @param null   $default
     *
     * @return array
     */
    public function get($key, $default = null)
    {
        return call_user_func_array($this->fn, [ $key, $default ]);
    }
}
