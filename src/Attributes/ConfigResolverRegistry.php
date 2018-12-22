<?php

namespace Codex\Attributes;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class ConfigResolverRegistry
{
    protected $resolvers = [
        'scalar'                => ConfigResolver::class . '@scalar',
        'string'                => ConfigResolver::class . '@scalar',
        'boolean'               => ConfigResolver::class . '@boolean',
        'integer'               => ConfigResolver::class . '@integer',
        'array'                 => ConfigResolver::class . '@array',
        'dictionary'            => ConfigResolver::class . '@dictionary',
        'array.scalarPrototype' => ConfigResolver::class . '@arrayScalar',
        'array.arrayPrototype'  => ConfigResolver::class . '@arrayArray',
        'array.recursive'       => ConfigResolver::class . '@arrayRecursive',
        'recurse'       => ConfigResolver::class . '@recurse',
    ];

    /**
     * register method
     *
     * @param string|\Codex\Attributes\AttributeDefinitionType $attributeType
     * @param string|callable                                  $resolver
     *
     * @return void
     */
    public function register($attributeType, $resolver)
    {
        if ( ! $attributeType instanceof AttributeDefinitionType) {
            $attributeType = new AttributeDefinitionType($attributeType);
        }
    }

    public function call(string $name, AttributeDefinition $attribute, NodeDefinition $target)
    {

        $resolver = $this->resolvers[ $name ];
        return app()->call($resolver, compact('name', 'attribute', 'target'));
    }
}
