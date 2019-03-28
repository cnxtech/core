<?php

namespace Codex\Mergable\Commands;

use Codex\Mergable\ParameterPostProcessor;
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;

class AggregateAttributes
{
    /** @var array */
    protected $attributes;

    /** @var array */
    protected $parameters;

    /** @var bool */
    protected $attributesInParameters;

    /** @var bool */
    protected $ignoreParameterExceptions;

    /**
     * AggregateAttributes constructor.
     *
     * @param array $attributes
     * @param array $parameters
     * @param bool  $attributesInParameters
     */
    public function __construct(array $attributes = [], array $parameters = [], bool $attributesInParameters = true, bool $ignoreParameterExceptions = true)
    {
        $this->attributes                = $attributes;
        $this->parameters                = $parameters;
        $this->attributesInParameters    = $attributesInParameters;
        $this->ignoreParameterExceptions = $ignoreParameterExceptions;
    }

    public function handle()
    {
        $attributes = $this->attributes;
        $parameters = $this->parameters;
        if ($this->attributesInParameters) {
            $parameters = array_replace_recursive($attributes, $this->parameters);
        }
        $aggregator = new ConfigAggregator(
            [ new ArrayProvider(compact('attributes')) ],
            null,
            [ new ParameterPostProcessor($parameters, $this->ignoreParameterExceptions) ]
        );
        return data_get($aggregator->getMergedConfig(), 'attributes', []);
    }
}
