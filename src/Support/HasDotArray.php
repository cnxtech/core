<?php


namespace Codex\Support;


trait HasDotArray
{

    protected $items = [];

    /**
     * has method
     *
     * @param string|array $keys
     *
     * @return bool
     */
    public function has($keys)
    {
        return array_has($this->items, $keys);
    }

    /**
     * get method
     *
     * @param string|array $key
     * @param              $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return data_get($this->items, $key, $default);
    }

    /**
     * set method
     *
     * @param string|array $key
     * @param              $value
     * @param bool         $overwrite
     *
     * @return static
     */
    public function set($key, $value, $overwrite = true)
    {
        data_set($this->items, $key, $value, $overwrite);
        return $this;
    }

//    public function unset($keys)
//    {
//        array_forget($this->items, $keys);
//        return $this;
//    }

    public function push($key, $value)
    {
        $data   = $this->get($key, []);
        $data[] = $value;
        $this->set($key, $data);
        return $this;
    }

    /**
     * only method
     *
     * @param $keys
     *
     * @return \Codex\Support\DotArrayWrapper
     */
    public function only($keys)
    {
        $keys   = is_string($keys) ? func_get_args() : $keys;
        $result = static::make();
        foreach ($keys as $key) {
            $result->set($key, $this->get($key));
        }
        return $result;
    }

    public function keys()
    {
        return array_keys($this->items);
    }

    public function collect($key = null)
    {
        return $key === null ? collect($this->items) : collect($this->get($key, []));
    }

    /**
     * without method
     *
     * @param string|array $keys
     *
     * @return \Codex\Support\DotArrayWrapper
     */
    public function without($keys)
    {
        $keys = is_string($keys) ? func_get_args() : $keys;
//        return $this->clone()->unset($keys);
    }

    /**
     * merge method
     * unique = false === append (double values in arrays may occur)
     * unique = true === merge
     *
     * @param array $array
     * @param bool  $mergeWithSelf If false, a new instance is created with the resulting merge. If true, the array will be merged into the current instance
     * @param bool  $unique
     *
     * @return static
     */
    public function merge(array $array, $mergeWithSelf = false, $unique = true)
    {
        $result = \Illuminate\Support\Arr::merge($this->items, $array, $unique);
        if ($mergeWithSelf) {
            $this->items = $result;
            return $this;
        }
        return static::make($result);
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->items);
    }

//    public function clone()
//    {
//        return static::make($this->items);
//    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return collect($this->items)->toArray();
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }

    /**
     * getIterator method
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * make method
     *
     * @param array $array
     *
     * @return static
     */
    public static function make(array $array = [])
    {
        $wrapper        = new static();
        $wrapper->items = $array;
        return $wrapper;
    }
}
