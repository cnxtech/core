<?php


namespace Codex\Contracts\Models;


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
