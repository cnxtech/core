<?php

namespace Codex\Models;

use Codex\Contracts\Models\Model as ModelContract;
use Illuminate\Support\Collection;

class Storage extends Collection
{
    /** @var \Codex\Contracts\Models\Model */
    protected $model;

    public function __construct(ModelContract $model)
    {
        $this->model = $model;
        parent::__construct();
    }

    public function getSet($key, callable $value)
    {
        if ( ! $this->has($key)) {
            $this->put($key, app()->call($value, [ $this->model ]));
        }
        return $this->get($key);
    }

    public function getModel()
    {
        return $this->model;
    }

}
