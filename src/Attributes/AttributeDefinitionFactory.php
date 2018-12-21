<?php

namespace Codex\Attributes;

class AttributeDefinitionFactory
{
    public static function group(string $name)
    {
        return app()->make(AttributeDefinitionGroup::class, compact('name'));
    }

    public static function attribute(string $name, $type, $apiType = null, $default = null)
    {
        /** @var AttributeDefinition $attribute */
        $attribute = app()->make(AttributeDefinition::class, compact('name', 'type', 'apiType'));
        if ($default !== null) {
            $attribute->setDefault($default);
        }
        return $attribute;
    }
}
