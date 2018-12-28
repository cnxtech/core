<?php

namespace Codex\Attributes;

class AttributeSchemaGenerator
{
    /** @var AttributeDefinitionRegistry */
    protected $registry;

    protected $types;

    /**
     * AttributeSchemaGenerator constructor.
     *
     * @param AttributeDefinitionRegistry $registry
     */
    public function __construct(AttributeDefinitionRegistry $registry)
    {
        $this->registry = $registry;
    }


    public function generate()
    {
        $this->types = [];
        foreach ($this->registry->keys() as $groupName) {
            $group                     = $this->registry->resolveGroup($groupName);
            $groupType                 = 'extend type ' . studly_case(str_singular($group->name));
            $this->types[ $groupType ] = [];
            $this->generateChildren($group->children, $this->types[ $groupType ]);
        }

        $generated = collect($this->types)->map(function ($fields, $type) {
            $fields = collect($fields)->map(function ($type, $name) {
                return "\t{$name}: {$type}";
            })->implode("\n");
            return "{$type} {\n{$fields}\n}";
        })->implode("\n");

        return $generated;
    }

    /** @param AttributeDefinition[] $children */
    protected function generateChildren(array $children, array &$parent)
    {
        foreach ($children as $child) {
            if($child->noApi === true){
                continue;
            }
            $apiType                = $child->apiType;
            $parent[ $child->name ] = $this->toFieldTypeString($apiType);
            if ($apiType->new || $apiType->extend) {
                $this->types[ $this->toObjectTypeString($apiType) ] = [];
                if ($child->hasChildren()) {
                    $this->generateChildren($child->children, $this->types[ $this->toObjectTypeString($apiType) ]);
                }
            }
        }
    }

    protected function toFieldTypeString(AttributeDefinitionApiType $apiType)
    {
        $parts = [ $apiType->name ];
        if ($apiType->nonNull) {
            $parts[] = '!';
        }
        if ($apiType->array) {
            array_unshift($parts, '[');
            $parts[] = ']';
        }
        if ($apiType->array && $apiType->arrayNonNull) {
            $parts[] = '!';
        }
        return implode('', $parts);
    }

    protected function toObjectTypeString(AttributeDefinitionApiType $apiType)
    {
        return ($apiType->extend ? 'extend ' : '') . 'type ' . $apiType->name;
    }
}
