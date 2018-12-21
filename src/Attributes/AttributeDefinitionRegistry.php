<?php

namespace Codex\Attributes;


/**
 * @property-read AttributeDefinitionGroup $codex
 * @property-read AttributeDefinitionGroup $projects
 * @property-read AttributeDefinitionGroup $revisions
 * @property-read AttributeDefinitionGroup $documents
 */
class AttributeDefinitionRegistry
{
    /** @var AttributeDefinitionGroup[] */
    protected $groups = [];

    public function __construct()
    {
        $codex     = $this->addGroup('codex');
        $projects  = $this->addGroup('projects')->setParentGroup($codex);
        $revisions = $this->addGroup('revisions')->setParentGroup($projects);
        $documents = $this->addGroup('documents')->setParentGroup($revisions);
    }

    protected function addGroup(string $name)
    {
        return $this->groups[ $name ] = AttributeDefinitionFactory::group($name);
    }

    public function keys()
    {
        return array_keys($this->groups);
    }

    public function getGroup(string $name)
    {
        return $this->groups[ $name ];
    }

    /**
     * Returns the group after ensuring all the attributes that should inherit and merge are copied from the parent
     *
     * @param string $name
     *
     * @return AttributeDefinitionGroup
     */
    public function resolveGroup(string $name)
    {
        $group = $this->getGroup($name);
        if ( ! $group->hasParentGroup()) {
            return $group;
        }
        $parent = $group->parentGroup;
        foreach (array_merge($group->inheritKeys, $group->mergeKeys) as $sourceKey => $targetKey) {
            if (is_int($sourceKey)) {
                $sourceKey = $targetKey;
            }
            if ( ! $parent->hasChild($sourceKey) || $group->hasChild($targetKey)) {
                continue;
            }
            $source = $parent->getChild($sourceKey);
            $this->addSourceToTarget($group, $targetKey, $source);
        }
        return $group;
    }

    protected function addSourceToTarget($target, $targetKey, $source)
    {
        $targetChild = $target->add($targetKey, $source->type->getValue(), $source->apiType->name, $source->default);
        if ($source->hasChildren()) {
            foreach ($source->children as $child) {
                $this->addSourceToTarget($targetChild, $child->name, $child);
            }
        }
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __get($key)
    {
        if (array_key_exists($key, $this->groups)) {
            return $this->getGroup($key);
        }
    }

}
