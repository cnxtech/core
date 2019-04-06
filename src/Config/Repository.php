<?php

namespace Codex\Config;

use Codex\Contracts\Config\Repository as RepositoryContract;
use Codex\Mergable\ParameterPostProcessor;
use Illuminate\Config\Repository as BaseRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;


class Repository extends BaseRepository implements RepositoryContract
{
    use DispatchesJobs;

    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $app;

    /** @var \Codex\Config\ConfigProcessor */
    protected $processor;

    /** @var bool */
    protected $useProcessor = true;

    public function __construct(Application $app, \Illuminate\Contracts\Config\Repository $config, ConfigProcessor $processor)
    {
        $this->config    = $config;
        $this->app       = $app;
        $this->processor = $processor;
        parent::__construct();
    }

    /**
     * Get the specified configuration value.
     *
     * @param array|string $key
     * @param mixed        $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }
        $value = $this->config->get($key, $default);
        $value = $this->process($value);
        return $value;
    }

    public function raw($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    public function rawMany($keys)
    {
        return $this->config->get($keys);
    }

    /**
     * Get many configuration values.
     *
     * @param array $keys
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
        $value = $this->process($value);
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
        return $this->process($this->config->all());
    }

    protected function process($value)
    {
        $agg   = new ConfigAggregator(
            [
                function() use ($value) {
                    $config = new \Illuminate\Config\Repository();
                    $config->set($this->config->all());
                    $config->set('value',$value);
                    return $config->all();
                },
//                new ArrayProvider([ 'config' => $this->config, 'value' => $value ])
            ],
            null,
            [ new ParameterPostProcessor(array_merge($this->config->all(), $value), true) ]
        );
        $value = data_get($agg->getMergedConfig(), 'value',$value);
        return $value;
        if ( ! $this->useProcessor) {
            return $value;
        }
        $this->processor->setValue('codex', $this->app[ 'codex' ]);
        return $this->processor->process($value);
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return bool
     */
    public function isUseProcessor(): bool
    {
        return $this->useProcessor;
    }

    /**
     * @param bool $useProcessor
     */
    public function setUseProcessor(bool $useProcessor): void
    {
        $this->useProcessor = $useProcessor;
    }


}
