<?php

namespace Codex\Mergable\Commands;

use Codex\Attributes\AttributeConfigBuilderGenerator;
use Codex\Attributes\AttributeDefinitionGroup;
use Codex\Contracts\Mergable\Mergable;
use Codex\Hooks;
use Symfony\Component\Config\Definition\Processor;
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregatorParameters\ParameterPostProcessor;

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
        $target     = $this->getTarget();
        $attributes = $this->getAttributes();
        $builder    = $generator->generateGroup($target);
        $processor  = new Processor();
        $final      = $processor->process($builder->buildTree(), [ $attributes ]);
        $final      = $this->processParameters($final);
        $final      = Hooks::waterfall('command.process_attributes', $final, [ $target ]);
        return $final;
    }

    public function getTarget()
    {
        $target = $this->target;
        if ($target instanceof Mergable) {
            $target = $target->getAttributeDefinitions()->name;
        } elseif ($target instanceof AttributeDefinitionGroup) {
            $target = $target->name;
        }
        return $target;
    }

    public function getAttributes()
    {
        return $this->processParameters($this->attributes);
    }

    public function processParameters($config)
    {
        $aggregator = new ConfigAggregator(
            [ new ArrayProvider(compact('config')) ],
            null,
            [ new ParameterPostProcessor($config) ]
        );
        return data_get($aggregator->getMergedConfig(), 'config', []);
    }
}
