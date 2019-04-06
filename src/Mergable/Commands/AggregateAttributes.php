<?php

namespace Codex\Mergable\Commands;

use Codex\Config\ConfigProcessor;
use Illuminate\Contracts\Foundation\Application;

class AggregateAttributes
{
    /** @var array */
    protected $attributes;

    /** @var array */
    protected $values;

    public function __construct($attributes = [], $values = [])
    {
        $this->attributes = $attributes;
        $this->values     = $values;
    }

    public function handle(Application $app)
    {
        $processor = new ConfigProcessor($app, $app[ 'codex.config.language' ]);
        $processor->setValue('app', $app);
        $processor->setValue('config', $app[ 'config' ]);

        foreach($this->attributes as $key=>$value){
            $processor->setValue($key, $value);
        }
        foreach ($this->values as $key => $value) {
            $processor->setValue($key, $value);
        }
        return $processor->process($this->attributes);

//        $attributes = $this->attributes;
//        $parameters = $this->parameters;
//        if ($this->attributesInParameters) {
//            $parameters = array_replace_recursive($attributes, $this->parameters);
//        }
//        $aggregator = new ConfigAggregator(
//            [ new ArrayProvider(compact('attributes')) ],
//            null,
//            [ new ParameterPostProcessor($parameters, $this->ignoreParameterExceptions) ]
//        );
//        return data_get($aggregator->getMergedConfig(), 'attributes', []);
    }
}
