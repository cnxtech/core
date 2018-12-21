<?php


namespace Codex\Attributes\Visitor;


use Codex\Attributes\AttributeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface NodeDefinitionVisitor
{
    public function visit(NodeDefinition $definition);
    public function depart(NodeDefinition $definition);
}
