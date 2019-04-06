<?php


namespace Codex\Mergable;


use ArrayAccess;
use Codex\Support\HasDotArray;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use IteratorAggregate;
use Codex\Contracts\Mergable\Model as ModelContract;
class ModelParameterWrapper implements Arrayable, ArrayAccess, IteratorAggregate, Countable
{
    use Macroable;
    use HasDotArray {
        get as dotGet;
        set as dotSet;
        has as dotHas;
        keys as dotKeys;
    }

    /** @var \Codex\Contracts\Mergable\Model */
    protected $model;

    /**
     * ModelParameterWrapper constructor.
     *
     * @param \Codex\Contracts\Mergable\Model $model
     */
    public function __construct(ModelContract $model)
    {
        $this->model = $model;

    }

    public function has($keys)
    {
        $has=$this->dotHas($keys);
        if(!$has && is_string($keys)){
            $this->model->hasAttribute($keys);
        }
        return $this->dotHas($keys);
    }

    public function get($key, $default = null)
    {
        return $this->dotGet($key,$default);
    }

    public function set($key, $value, $overwrite = true)
    {
        return $this->dotSet($key, $value, $overwrite);
    }

    public function keys()
    {
        return $this->dotKeys();
    }


    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name,$value);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function __unset($name)
    {
        return $this->unset($name);
    }


}
