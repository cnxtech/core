<?php


namespace Codex\Attributes\Commands;


use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionRegistry;
use Codex\Attributes\AttributeType as T;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class BuildDefinitionConfig
{
    /** @var \Codex\Attributes\AttributeDefinition */
    protected $definition;

    /**
     * @var \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    protected $builder;

    public function __construct(AttributeDefinition $definition)
    {
        $this->definition = $definition;
        $this->builder    = new TreeBuilder($definition->name);
        $this->builder->root($definition->name);
        $this->builder->getRootNode();
    }

    public function handle(AttributeDefinitionRegistry $registry)
    {
        $definition      = $registry->resolve($this->definition);
        $builderRootNode = $this->builder->getRootNode();
        $builderRootNode->ignoreExtraKeys(true);
        $builderRootNode->addDefaultsIfNotSet();

        $this->traverseChildren($definition->children, $builderRootNode);

        return $this->builder->buildTree();
    }

    /**
     * @param \Illuminate\Support\Collection|AttributeDefinition[]             $children
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    protected function traverseChildren($children, ArrayNodeDefinition $node)
    {
        foreach ($children as $definition) {
            $this->handleChild($definition, $node);
        }
    }

    /**
     * @param \Codex\Attributes\AttributeDefinition                                                                                        $definition
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition $parentNode
     */
    public function handleChild(AttributeDefinition $definition, ArrayNodeDefinition $parentNode)
    {
        /** @var ArrayNodeDefinition $node */
        $type = $definition->type;

        if ( ! $type->isChildType()) {
            $node = $parentNode->children()->node(
                $definition->name,
                T::getConfigNodeTypeName($type)
            );
        } elseif ($type->is(T::RECURSE)) {
            $node = $this->handleRecurse($definition, $parentNode);
        } elseif ($type->is(T::RECURSIVE)) {
            $this->handleRecursive($definition, $parentNode);
            return;
        } elseif ($type->is(T::ARRAY)) {
            $node = $this->handleArray($definition, $parentNode);
        } elseif ($type->is(T::MAP)) {
            $node = $this->handleMap($definition, $parentNode);
        }
        if (isset($definition->default)) {
            $node->defaultValue($definition->default);
        }
        if ($definition->required) {
            $node->isRequired();
        }
        if ($definition->children->isNotEmpty()) {
            $parentNode->ignoreExtraKeys(true);
            $this->traverseChildren($definition->children, $node);
        }
    }

    protected function handleRecurse(AttributeDefinition $definition, ArrayNodeDefinition $parentNode)
    {
        $parentNode->addDefaultsIfNotSet();
        $parentNode->ignoreExtraKeys(true);
        return $parentNode->children()->arrayNode($definition->name);
    }

    protected function handleRecursive(AttributeDefinition $definition, ArrayNodeDefinition $parentNode)
    {
        $children = $definition->children;
        $target   = $parentNode;
        /** @var AttributeDefinition $recurseChild */
        $recurseChild = $children->filter(function (AttributeDefinition $definition) {
            return $definition->type->is(T::RECURSE);
        })->first();

        $node = static::getArrayRecursiveDefinition($definition->name, $recurseChild->name, function (NodeBuilder $builder, ArrayNodeDefinition $node) use ($children) {
            $children->filter(function (AttributeDefinition $definition) {
                return false === $definition->type->is(T::RECURSE);
            })->each(function (AttributeDefinition $definition) use ($builder, $node) {
                $this->handleChild($definition, $node);
            });
        });

        $target->append($node);
        return $node;
    }

    protected function handleArray(AttributeDefinition $definition, ArrayNodeDefinition $parentNode)
    {
        $type        = $definition->type;
        $childType   = $type->getChildType();
        $hasChildren = $definition->hasChildren();
        if ($childType->is(T::MAP)) {
            $node = $parentNode->children()->arrayNode($definition->name);
            $node->ignoreExtraKeys();
            $node = $node->arrayPrototype();
            $node->ignoreExtraKeys();
            return $node;
        }
        $node = $parentNode->children()->arrayNode($definition->name);
        $node->ignoreExtraKeys();

        if ( ! $hasChildren) {
//            $node = $node->variablePrototype();
            $node = $node->prototype($childType->toConfigNodeTypeName());
        } else {
            $node = $node->variablePrototype();
        }
        return $node;
    }

    protected function handleMap(AttributeDefinition $definition, ArrayNodeDefinition $parentNode)
    {
        $hasChildren = $definition->hasChildren();
        $node        = $parentNode->children()->arrayNode($definition->name);
        $node->ignoreExtraKeys();;
        if ( ! $hasChildren) {
            $node = $node->variablePrototype();
        }
        return $node;
    }

    public static function buildRecursive(ArrayNodeDefinition $node, $recurseChildName, callable $addFn)
    {
        $child = $node->children();
        $addFn($child, $node);
        $builder  = new TreeBuilder($recurseChildName, 'variable');
        $rootNode = $builder->getRootNode();
        $rootNode->defaultValue([])
            ->validate()
            ->ifTrue(function ($v) {
                return ! is_array($v);
            })
            ->thenInvalid('Element must be array')
            ->always(function (iterable $children) use ($builder, $recurseChildName, $addFn) {
                $config = [];
                foreach ($children as $name => $child) {
                    $node = $builder->root($name);
                    $node->addDefaultsIfNotSet();
                    static::buildRecursive($node, $recurseChildName, $addFn);
                    $config[ $name ] = $node->getNode(true)->finalize($child);
                }
                return $config;
            });
        $child->append($rootNode);
        return $child;
    }

    public static function getArrayRecursiveDefinition($name, $recurseName, callable $addFn)
    {
        $node = static::node($name);
        static::buildRecursive($node->arrayPrototype(), $recurseName, $addFn);
        return $node;
    }

    public static function builder($name, $type = 'array')
    {
        return new TreeBuilder($name, $type);
    }

    public static function node($name, $type = 'array')
    {
        return static::builder($name, $type)->getRootNode();
    }
}
