<?php

namespace Codex\Attributes;

use Illuminate\Support\Arr;

class AttributeDefinitionGroup
{
    use WithAttributeDefinitionChildren;

    /** @var string */
    public $name;

    /** @var AttributeDefinitionGroup|null */
    public $parentGroup;

    /** @var string[] */
    public $inheritKeys = [];

    /** @var string[] */
    public $mergeKeys = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addInheritKeys($keys)
    {
        foreach (Arr::wrap($keys) as $key) {
            $this->inheritKeys[] = $key;
        }
        return $this;
    }

    public function addMergeKeys($keys)
    {
        foreach (Arr::wrap($keys) as $key) {
            $this->mergeKeys[] = $key;
        }
        return $this;
    }

    public function setParentGroup(AttributeDefinitionGroup $parentGroup)
    {
        $this->parentGroup = $parentGroup;
        return $this;
    }

    public function hasParentGroup()
    {
        return isset($this->parentGroup);
    }

}
