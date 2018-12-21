<?php

namespace Codex\Attributes;

use Codex\Attributes\Visitor\NodeDefinitionVisitor;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class ConfigGeneratorVisitor implements NodeDefinitionVisitor
{

    public function visit(NodeDefinition $definition)
    {
        return;
    }

    public function depart(NodeDefinition $definition)
    {
        return;
    }
}
