<?php

namespace Codex\Models;

use Codex\Contracts\Models\ParentInterface;
use Codex\Models\Concerns\HasParent;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class ModelCollection extends EloquentCollection
{
    use DispatchesJobs;
    use HasParent {
        _setParentAsProperty as setParent;
    }

    protected $parent;

    protected $resolved = false;

    protected $loadable = [];

    public function __construct(array $items = [], ParentInterface $parent = null)
    {
        $this->setParent($parent);
        parent::__construct($items);
    }

    /**
     * resolveModels method
     *
     * @return array
     */
    abstract protected function resolveLoadable();

    /**
     * resolveModels method
     *
     * @return mixed
     */
    abstract protected function makeModel($key);

    /**
     * getDefault method
     *
     * @return mixed
     */
    abstract public function getDefaultKey();

    public function loadable()
    {
        return collect($this->resolve()->loadable);
    }

    /**
     * makeAll method
     *
     * @return $this
     */
    public function makeAll()
    {
        $this->all();
        return $this;
    }

    public function getDefault()
    {
        return $this->get($this->getDefaultKey());
    }

    /**
     * getLoadable method
     *
     * @param $key
     *
     * @return mixed
     */
    protected function getLoadable($key)
    {
        $this->resolve();
        return $this->loadable[ $key ];
    }

    /**
     * resolve method
     *
     * @param bool $force
     *
     * @return static
     */
    public function resolve($force = false)
    {
        if ( ! $this->resolved || $force) {
            $this->loadable = $this->resolveLoadable();
            $this->resolved = true;
        }
        return $this;
    }

    /**
     * get method
     *
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $this->resolve();
        if ( ! $this->hasModel($key) && $this->has($key)) {
            $model = $this->makeModel($key);
            $this->push($model);
        }

        return $this->find($key, $default);
    }

    /**
     * toRelationship method
     *
     * @return \Codex\Models\EloquentCollection|static[]
     */
    public function toRelationship()
    {
        return with(new EloquentCollection($this->resolve()->keys()))->transform(function ($key) {
            return $this->get($key);
        });
    }

    public function all()
    {
        return array_filter(
            array_map(function ($key) {
                return $this->get($key);
            }, $this->resolve()->keys()),
            function ($model) {
                return $model->isEnabled();
            });
    }

    protected function hasModel($key)
    {
        return ! $this->where('key', '=', $key)->isEmpty();
    }

    /**
     * has method
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        return \array_key_exists($key, $this->loadable);
    }

    /**
     * keys method
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->loadable);
    }

    public function getGraphSelection(array $attributes)
    {
        $data = array_map(function (Model $model) use ($attributes) {
            return $model->getGraphSelection($attributes);
        }, $this->items);

        return $data;
    }

    public function where($key, $operator = null, $value = null)
    {
        return parent::where($key, $operator, $value)->setParent($this->getParent());
    }

    public function orderBy($column, $order)
    {
        $method = $order === 'ASC' ? 'sortBy' : 'sortByDesc';
        $sorted = $this->$method($column);
        $sorted->values()->setParent($this->getParent());
        return $sorted;
    }

    /**
     * Set the resolved value
     *
     * @param bool $resolved
     *
     * @return ModelCollection
     */
    public function setResolved($resolved)
    {
        $this->resolved = $resolved;
        return $this;
    }

    /**
     * Set the loadable value
     *
     * @param array $loadable
     *
     * @return ModelCollection
     */
    public function setLoadable($loadable)
    {
        $this->loadable = $loadable;
        return $this;
    }

    public function enabled()
    {
        return $this->filter(function (Model $model) {
            return $model->isEnabled();
        });
    }

    public function disabled()
    {
        return $this->filter(function (Model $model) {
            return ! $model->isEnabled();
        });
    }

}
