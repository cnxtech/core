<?php

namespace Codex\Attributes;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class ConfigResolverRegistry
{
    protected $resolvers = [
        'scalar'                    => ConfigResolver::class . '@scalar',
        'string'                    => ConfigResolver::class . '@scalar',
        'boolean'                   => ConfigResolver::class . '@boolean',
        'integer'                   => ConfigResolver::class . '@integer',
        'array'                     => ConfigResolver::class . '@array',
        'dictionary'                => ConfigResolver::class . '@dictionary',
        'dictionaryPrototype'                => ConfigResolver::class . '@dictionaryPrototype',
        'array.dictionaryPrototype' => ConfigResolver::class . '@arrayDictionary',
        'array.scalarPrototype'     => ConfigResolver::class . '@arrayScalar',
        'array.arrayPrototype'      => ConfigResolver::class . '@arrayArray',
        'array.recursive'           => ConfigResolver::class . '@arrayRecursive',
        'recurse'                   => ConfigResolver::class . '@recurse',
    ];

    public function set(string $type, $resolver)
    {
        $this->resolvers[$type] = $resolver;
        return $this;
    }

    public function call(string $name, AttributeDefinition $attribute, NodeDefinition $target)
    {

        $resolver = $this->resolvers[ $name ];
        return app()->call($resolver, compact('name', 'attribute', 'target'));
    }
}
