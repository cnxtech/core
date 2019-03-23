<?php

namespace Codex\Attributes;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;


class ConfigResolver
{
    /** @var \Codex\Attributes\AttributeDefinitionRegistry */
    protected $registry;

    /** @var \Codex\Attributes\ConfigResolverRegistry */
    protected $resolvers;

    /**
     * ConfigResolver constructor.
     *
     * @param \Codex\Attributes\AttributeDefinitionRegistry $registry
     * @param \Codex\Attributes\ConfigResolverRegistry      $resolvers
     */
    public function __construct(AttributeDefinitionRegistry $registry, ConfigResolverRegistry $resolvers)
    {
        $this->registry  = $registry;
        $this->resolvers = $resolvers;
    }

    protected function addDefault(NodeDefinition $node, AttributeDefinition $attribute)
    {
        if ($attribute->hasDefault()) {
            $node->defaultValue(function () use ($attribute) {
                return $attribute->resolveDefault();
            });
        }
    }

    public function default(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $node = $target->children()->node($attribute->name, $attribute->type->toNodeType());
        $this->addDefault($node, $attribute);
        return $node;
    }

    public function scalar(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $node = $target->children()->node($attribute->name, 'scalar');
        $this->addDefault($node, $attribute);
        return $node;
    }

    public function boolean(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $node = $target->children()->node($attribute->name, 'boolean');
        $this->addDefault($node, $attribute);
        return $node;
    }

    public function integer(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $node = $target->children()->node($attribute->name, 'integer');
        $this->addDefault($node, $attribute);
        return $node;
    }

    public function array(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);
        $node = $target->children()->arrayNode($attribute->name);
        $node->addDefaultsIfNotSet();
        return $node;
    }

    public function dictionary(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);
        $node = $target->children()->arrayNode($attribute->name);
        $node->ignoreExtraKeys(true);
        return $node;
    }
    public function dictionaryPrototype(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);
        $node = $target->children()->arrayNode($attribute->name);
        $arrayProto = $node->arrayPrototype();
        $scalarProto = $arrayProto->variablePrototype();
        $arrayProto->ignoreExtraKeys(true)->normalizeKeys(false);

        return $scalarProto;
    }

    public function arrayDictionary(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);
        $node = $target->children()->arrayNode($attribute->name);
        $node->useAttributeAsKey('name');
        $node = $node->arrayPrototype();
        return $node;
    }

    public function arrayScalar(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $target->normalizeKeys(false);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);
        $node = $target->children()->arrayNode($attribute->name);
        $node->normalizeKeys(false);
        if (isset($attribute->default) && is_array($attribute->default)) {
            $node->defaultValue($attribute->default);
        }
        $node->validate()->always(function ($value) {
            return $value;
        });
        $node->ignoreExtraKeys(true);
        $node = $node->scalarPrototype();

        return $node;
    }

    public function arrayArray(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);
        $node = $target->children()->arrayNode($attribute->name);
        $node = $node->arrayPrototype();
        return $node;
    }

    public function arrayRecursive(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $children = collect($attribute->children);
//        $attribute->children = [];
        $target->attribute('attribute', $attribute);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);

        $recurse = $children->filter(function (AttributeDefinition $definition) {
            return $definition->type->is(AttributeDefinitionType::RECURSE());
        })->first();

        $node = $this->getArrayRecursiveDefinition($attribute->name, $recurse->name, function (NodeBuilder $node) use ($children) {
            $children->filter(function (AttributeDefinition $definition) {
                return false === $definition->type->is(AttributeDefinitionType::RECURSE());
            })->each(function (AttributeDefinition $attribute) use ($node) {
                $child = $this->resolvers->call(
                    $attribute->type->toNodeType(),
                    $attribute,
                    static::node($attribute->name)
                );
                $node->append($child);
            });
        });

        $target->append($node);

        return $node;
    }

    public function recurse(AttributeDefinition $attribute, ArrayNodeDefinition $target)
    {
        $target->attribute('attribute', $attribute);
        $target->addDefaultsIfNotSet();
        $target->ignoreExtraKeys(true);
        $node = $target->children()->arrayNode($attribute->name);
        $node = $node->arrayPrototype();
        return $node;
    }


    protected function buildArrayRecursiveDefinition(ArrayNodeDefinition $node, $recurseName, callable $addFn)
    {


        $child = $node->children();

        $addFn($child);

        $builder = static::builder($recurseName, 'variable');
        $def     = $builder->getRootNode();
        $def->defaultValue([])
            ->validate()
            ->ifTrue(function ($v) {
                return ! is_array($v);
            })
            ->thenInvalid('The element must be an array.')
            ->always(function (iterable $children) use ($builder, $def, $recurseName, $addFn) {
                $config = [];
                foreach ($children as $name => $child) {
                    $node = $builder->root($name);
                    $node->addDefaultsIfNotSet();
                    $this->buildArrayRecursiveDefinition($node, $recurseName, $addFn);
                    $config[ $name ] = $node->getNode(true)->finalize($child);
                }
                return $config;
            });

        $child->append($def);
    }

    protected function getArrayRecursiveDefinition($name, $recurseName, callable $addFn)
    {
        $node = static::node($name);
        $this->buildArrayRecursiveDefinition($node->arrayPrototype(), $recurseName, $addFn);
        return $node;
    }

    public static function builder($name, $type = 'array')
    {
        return new TreeBuilder($name, $type);
    }

    /**
     * node method
     *
     * @param        $name
     * @param string $type
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition|\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public static function node($name, $type = 'array')
    {
        return static::builder($name, $type)->getRootNode();
    }

}
