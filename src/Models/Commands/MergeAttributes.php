<?php

namespace Codex\Models\Commands;

use Codex\Contracts\Models\Model;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;


class MergeAttributes
{
    use DispatchesJobs;

    protected $target;

    /** @var array */
    protected $attributes;

    public function __construct(Model $target, array $attributes = [])
    {
        $this->target     = $target;
        $this->attributes = $attributes;
    }

    public function handle()
    {
        $definition = $this->target->getDefinition();
        // 1 : gather all the arrays needed
        $defaultAttributes = $this->target->getDefaultAttributes();
        $parentAttributes  = $this->target->getParentAttributes();
        $attributes        = $this->target->getAttributes();
        if ($defaultAttributes instanceof Repository) {
            $defaultAttributes = $defaultAttributes->all();
        }
        $inherited = $this->getInheritedParentAttributes();


        // 2 : merge all the gathered arrays
        $result = Arr::merge($inherited, $defaultAttributes);
        $result = Arr::merge($result, $attributes);
        foreach ($definition->mergeKeys as $mergeKey) {
            $result = Arr::merge($result, data_get($parentAttributes, $mergeKey, []));
        }
        $result = Arr::merge($result, $this->attributes);

        // 3 : Generate and build the Config tree from attribute definitions
        // 4 : Filter the merged result to only include nodes defined/accepted with the target
        $final = $this->dispatch(new ProcessAttributes($this->target, $result));
        // 5 : Set the final result data on the target
        $this->target->setMergedAttributes($final);
    }

    protected function getInheritKeys()
    {
        $keys     = $this->target->getDefinition()->inheritKeys;
        $resolved = [];
        foreach ($keys as $sourceKey => $targetKey) {
            if (is_int($sourceKey)) {
                $sourceKey = $targetKey;
            }
            $resolved[ $sourceKey ] = $targetKey;
        }
        return $resolved;
    }

    protected function getInheritedParentAttributes()
    {
        $parentAttributes = $this->target->getParentAttributes();
        $attributes       = [];
        foreach ($this->getInheritKeys() as $sourceKey => $targetKey) {
            data_set($attributes, $targetKey, data_get($parentAttributes, $sourceKey));
        }
        return $attributes;
    }
}
