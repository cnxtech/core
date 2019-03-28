<?php

namespace Codex\Mergable\Commands;

use Codex\Attributes\AttributeConfigBuilderGenerator;
use Codex\Contracts\Mergable\ChildInterface;
use Codex\Contracts\Mergable\Model;
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

    /**
     * handle method
     *
     * @param \Codex\Attributes\AttributeConfigBuilderGenerator $generator
     *
     * @return void
     */
    public function handle(AttributeConfigBuilderGenerator $generator)
    {
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
        foreach ($this->getMergeKeys() as $mergeKey) {
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
        $keys     = $this->target->getAttributeDefinitions()->inheritKeys;
        $resolved = [];
        foreach ($keys as $sourceKey => $targetKey) {
            if (is_int($sourceKey)) {
                $sourceKey = $targetKey;
            }
            $resolved[ $sourceKey ] = $targetKey;
        }
        return $resolved;
    }

    protected function getMergeKeys()
    {
        return $this->target->getAttributeDefinitions()->mergeKeys;
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
