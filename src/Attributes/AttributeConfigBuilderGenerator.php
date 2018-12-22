<?php

namespace Codex\Attributes;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class AttributeConfigBuilderGenerator
{
    /** @var AttributeDefinitionRegistry */
    protected $registry;

    /** @var \Codex\Attributes\ConfigResolverRegistry */
    protected $resolvers;

    /**
     * AttributeSchemaGenerator constructor.
     *
     * @param AttributeDefinitionRegistry $registry
     */
    public function __construct(AttributeDefinitionRegistry $registry, ConfigResolverRegistry $resolvers)
    {
        $this->registry  = $registry;
        $this->resolvers = $resolvers;
    }

    public function generate()
    {
        $builder = new TreeBuilder('root');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $builder->getRootNode();
        $rootNode->ignoreExtraKeys(true);
        $nodeBuilder = $rootNode->addDefaultsIfNotSet()->children();
        foreach ($this->registry->keys() as $groupName) {
            $nodeBuilder->append(
                $this->generateGroup($groupName)->getRootNode()
            );
        }
        return $builder;
    }

    public function generateGroup($groupName)
    {
        $group   = $this->registry->resolveGroup($groupName);
        $builder = new TreeBuilder($groupName);
        /** @var ArrayNodeDefinition $nodeDefinition */
        $nodeDefinition = $builder->getRootNode();
        $nodeDefinition->attribute('group', $group);
        $this->addNodeChildren($group->children, $nodeDefinition);
        return $builder;
    }

    /**
     * generateChildren method
     *
     * @param \Codex\Attributes\AttributeDefinition[]     $children
     * @param \App\Attributes\Builder\ArrayNodeDefinition $nodeDefinition
     *
     * @return void
     */
    protected function addNodeChildren(array $children, NodeDefinition $nodeDefinition)
    {
        /** @var NodeDefinition|ArrayNodeDefinition $node */
        foreach ($children as $child) {
            $node = $this->resolvers->call(
                $child->type->toNodeType(),
                $child,
                $nodeDefinition
            );
            if ($node instanceof ParentNodeDefinitionInterface && $child->hasChildren() && false === $child->type->is(AttributeDefinitionType::ARRAY_RECURSIVE(), AttributeDefinitionType::RECURSE())) {
                $this->addNodeChildren($child->children, $node);
            }
        }
    }
}
