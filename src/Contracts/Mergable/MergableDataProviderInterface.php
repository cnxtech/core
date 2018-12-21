<?php


namespace Codex\Contracts\Mergable;


interface MergableDataProviderInterface
{
    /**
     * get method
     *
     * @param string $key
     *
     * @param null   $default
     *
     * @return array
     */
    public function get($key, $default = null);
}
