<?php


namespace Codex\Contracts\Config;

/**
 * @mixin \Codex\Config\Repository
 */
interface Repository extends \Illuminate\Contracts\Config\Repository
{
    /**
     * Get the specified configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function raw($key, $default = null);

}
