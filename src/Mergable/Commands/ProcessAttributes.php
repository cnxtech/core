<?php

namespace Codex\Mergable\Commands;

use Codex\Attributes\AttributeConfigBuilderGenerator;
use Codex\Attributes\AttributeDefinitionGroup;
use Codex\Contracts\Mergable\Mergable;
use Symfony\Component\Config\Definition\Processor;

class ProcessAttributes
{
    protected $target;

    /** @var array */
    protected $attributes;

    public function __construct($target, array $attributes = [])
    {
        $this->target     = $target;
        $this->attributes = $attributes;
    }

    public function handle(AttributeConfigBuilderGenerator $generator)
    {
        $target = $this->target;
        if ($target instanceof Mergable) {
            $target = $target->getAttributeDefinitions()->name;
        } elseif ($target instanceof AttributeDefinitionGroup) {
            $target = $target->name;
        }

        $builder   = $generator->generateGroup($target);
        $processor = new Processor();
        $final     = $processor->process($builder->buildTree(), [ $this->attributes ]);

        return $final;
    }
}
