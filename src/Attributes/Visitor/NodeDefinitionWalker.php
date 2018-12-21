<?php

namespace Codex\Attributes\Visitor;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;

class NodeDefinitionWalker
{
    public function walk(NodeDefinition $nodeDefinition, NodeDefinitionVisitor $visitor)
    {
        $visitor->visit($nodeDefinition);
        if ($nodeDefinition instanceof ParentNodeDefinitionInterface) {
            foreach ($nodeDefinition->getChildNodeDefinitions() as $childNodeDefinition) {
                $this->walk($childNodeDefinition, $visitor);
            }
        }
        $visitor->depart($nodeDefinition);
    }
}
