<?php

namespace Codex\Attributes;

use Illuminate\Support\Collection;

/**
 * @property-read AttributeDefinition $codex
 * @property-read AttributeDefinition $projects
 * @property-read AttributeDefinition $revisions
 * @property-read AttributeDefinition $documents
 * @method AttributeDefinition get($key, $default = null)
 */
class AttributeDefinitionRegistry extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
        $codex     = $this->add('codex');
        $projects  = $this->add('projects')->parent($codex);
        $revisions = $this->add('revisions')->parent($projects);
        $documents = $this->add('documents')->parent($revisions);
    }


    public function push($name)
    {
        $this->put($name, with(new AttributeDefinition())->name($name)->type(AttributeType::MAP));
        return $this;
    }

    public function add(string $name)
    {
        $this->push($name);
        return $this->get($name);
    }

    public function child(string $name)
    {
        return $this->add($name);
    }

    public function resolve($definition)
    {
        if ( ! $definition instanceof AttributeDefinition) {
            $definition = $this->get($definition);
        }
        if ( ! $definition->hasParent()) {
            return $definition;
        }
        $parent = $definition->parent;
        foreach (array_merge($definition->inheritKeys, $definition->mergeKeys) as $sourceKey => $targetKey) {
            if (is_int($sourceKey)) {
                $sourceKey = $targetKey;
            }
            if ( ! $parent->children->has($sourceKey) || $definition->children->has($targetKey)) {
                continue;
            }
            $source = $parent->children->get($sourceKey);
            $this->addSourceToTarget($definition, $targetKey, $source);
        }
        return $definition;
    }

    protected function addSourceToTarget(AttributeDefinition $target, $targetKey, AttributeDefinition $source)
    {
        $targetChild = $target
            ->child($targetKey, $source->type)
            ->api($source->api)
            ->default($source->default)
            ->noApi($source->noApi);

        foreach ($source->children as $child) {
            $this->addSourceToTarget($targetChild, $child->name, $child);
        }
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        return parent::__get($key);
    }

}
