<?php

namespace Codex\Config;

use Codex\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Config\Repository as BaseRepository;
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregatorParameters\ParameterPostProcessor;

class Repository extends BaseRepository implements RepositoryContract
{
    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    /**
     * Repository constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(\Illuminate\Contracts\Config\Repository $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    /**
     * Get the specified configuration value.
     *
     * @param  array|string $key
     * @param  mixed        $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }
        $value = $this->config->get($key, $default);
        $value = $this->processParameters($value);
        return $value;
    }

    /**
     * Get many configuration values.
     *
     * @param  array $keys
     *
     * @return array
     */
    public function getMany($keys)
    {
        if (method_exists($this->config, 'getMany')) {
            $value = $this->config->getMany($keys);
        } else {
            $value = $this->config->get($keys);
        }
        $value = $this->processParameters($value);
        return $value;
    }

    public function has($key)
    {
        return $this->config->has($key);
    }

    public function set($key, $value = null)
    {
        $this->config->set($key, $value);
    }

    public function all()
    {
        return $this->processParameters($this->config->all());
    }

    protected function processParameters($config)
    {
        $aggregator = new ConfigAggregator(
            [ new ArrayProvider(compact('config')) ],
            null,
            [ new ParameterPostProcessor(collect($this->config->all())->filter(function($item, $key){
                return starts_with($key,'codex');
            })->toArray()) ]
        );
        return data_get($aggregator->getMergedConfig(), 'config', []);
    }

}
