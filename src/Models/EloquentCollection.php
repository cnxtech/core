<?php

namespace Codex\Models;

class EloquentCollection extends \Illuminate\Database\Eloquent\Collection
{
    public function get($key, $default = null)
    {
        return $this->find($key, $default);
    }

    public function show($relations)
    {
        if ($this->isNotEmpty()) {
            if (is_string($relations)) {
                $relations = func_get_args();
            }

            foreach ($this->items as $item) {
                if ( ! $item instanceof Model) {
                    continue;
                }
                $item->show($relations);
            }
        }

        return $this;
    }

    public function rehide($recursive = true)
    {
        $a = $this->forPage(1,2);
        $this->each->rehide($recursive);
        return $this;
    }
}
