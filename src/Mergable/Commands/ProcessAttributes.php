<?php

namespace Codex\Mergable\Commands;

use Codex\Attributes\AttributeConfigBuilderGenerator;
use Codex\Contracts\Mergable\ChildInterface;
use Codex\Contracts\Mergable\Model;
use Codex\Hooks;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Config\Definition\Processor;

class ProcessAttributes
{
    use DispatchesJobs;

    /** @var \Codex\Contracts\Mergable\Model */
    protected $target;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $parameters;

    public function __construct(Model $target, array $attributes = [], array $parameters = [])
    {
        $this->target     = $target;
        $this->attributes = $attributes;
        $this->parameters = $parameters;
    }

    public function handle(AttributeConfigBuilderGenerator $generator)
    {
        $target     = $this->getTargetName();
        $builder    = $generator->generateGroup($target);
        $processor  = new Processor();
        $attributes = $processor->process($builder->buildTree(), [ $this->attributes ]);
        $attributes = Hooks::waterfall('command.process_attributes', $attributes, [ $target ]);
        return $attributes;
    }

    protected function getTargetName()
    {
        return $this->target->getAttributeDefinitions()->name;
    }

}
