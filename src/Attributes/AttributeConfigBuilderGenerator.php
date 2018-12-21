<?php

namespace Codex\Attributes;

use Codex\Attributes\AttributeDefinitionType as Type;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class AttributeConfigBuilderGenerator
{
    /** @var AttributeDefinitionRegistry */
    protected $registry;

    protected $types;

    public $visitors = [

    ];


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

        if (method_exists($nodeDefinition, 'ignoreExtraKeys')) {
            $nodeDefinition->ignoreExtraKeys(true);
        }
        if (method_exists($nodeDefinition, 'addDefaultsIfNotSet')) {
            $nodeDefinition->addDefaultsIfNotSet();
        }
        foreach ($children as $child) {
            $childName = $child->name;
            $nodeType  = $child->type->toNodeType();
            $node = $nodeDefinition->children()->node($childName, $nodeType);
            $node->attribute('attribute', $child);

            $defaultIsScalarArray = $child->type->equals(Type::ARRAY()) && is_array($child->default) && empty($child->default);

            if ( ! $defaultIsScalarArray && $child->type->is(Type::ARRAY(), Type::DICTIONARY())) {
                if (method_exists($node, 'ignoreExtraKeys')) {
                    $node->ignoreExtraKeys(true);
                }
            }

            if ($defaultIsScalarArray) {
                    $node->scalarPrototype();
            }

            if ( ! $node instanceof ParentNodeDefinitionInterface) {
                if (isset($child->default)) {
                    if (Type::ARRAY()->equals($child->type)) {
                        $node->arrayPrototype()->ignoreExtraKeys(true);
                    } else {
                        $node->defaultValue($child->default);
                    }
                }
            }
            if ($node instanceof ParentNodeDefinitionInterface && $child->hasChildren()) {
                $this->addNodeChildren($child->children, $node);
            }
        }
    }
}
