<?php


namespace Codex\Attributes\Commands;


use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionRegistry;

class BuildDefinitionSchema
{
    protected $types;

    public function __construct()
    {
        $this->types = [];
    }

    public function handle(AttributeDefinitionRegistry $registry)
    {
        foreach ($registry->keys() as $definitionName) {
            $definition                     = $registry->resolve($definitionName);
            $definitionType                 = 'extend type ' . studly_case(str_singular($definition->name));
            if(!array_key_exists($definitionType, $this->types)) {
                $this->types[ $definitionType ] = [];
            }
            $this->generateChildren($definition->children, $this->types[ $definitionType ]);
        }

        $generated = collect($this->types)
            ->map(function ($fields, $type) {
            $fields = collect($fields)->map(function ($type, $name) {
                return "\t{$name}: {$type}";
            })->implode("\n");
            return "{$type} {\n{$fields}\n}";
        })->implode("\n");

        return $generated;
    }

    /** @param \Illuminate\Support\Collection|AttributeDefinition[] $children */
    protected function generateChildren($children, array &$parent)
    {
        foreach ($children as $child) {
            if ($child->noApi === true) {
                continue;
            }
            $api                    = $child->api;
            $parent[ $child->name ] = $this->toFieldTypeString($api);
            if ($api->new || $api->extend) {
                $this->types[ $this->toObjectTypeString($api) ] = [];
                if ($child->hasChildren()) {
                    $this->generateChildren($child->children, $this->types[ $this->toObjectTypeString($api) ]);
                }
            }
        }
    }

    /** @param \Codex\Attributes\ApiDefinition $api */
    protected function toFieldTypeString($api)
    {
        $parts = [ $api->name ];
        if ($api->nonNull) {
            $parts[] = '!';
        }
        if ($api->array) {
            array_unshift($parts, '[');
            $parts[] = ']';
        }
        if ($api->array && $api->arrayNonNull) {
            $parts[] = '!';
        }
        return implode('', $parts);
    }

    /** @param \Codex\Attributes\ApiDefinition $api */
    protected function toObjectTypeString($api)
    {
        return ($api->extend ? 'extend ' : '') . 'type ' . $api->name;
    }
}
