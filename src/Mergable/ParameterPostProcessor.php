<?php

namespace Codex\Mergable;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException as SymfonyParameterNotFoundException;
use Zend\ConfigAggregatorParameters\ParameterNotFoundException;

class ParameterPostProcessor
{
    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag
     */
    private $parameterBag;


    /** @var \Codex\Support\DotArrayWrapper|array */
    private $parameters;

    /** @var bool */
    private $ignoreParameterException;

    /** @var \Codex\Contracts\Mergable\Model */
    private $model;


    public function __construct($parameters = [], bool $ignoreParameterException = false)
    {
        $this->parameters               = $parameters;
        $this->ignoreParameterException = $ignoreParameterException;
    }


    public function __invoke(array $config): array
    {

        $bag = $this->getParameterBag();
        try {
            $bag->resolve();
        }
        catch (SymfonyParameterNotFoundException $exception) {
            if ( ! $this->ignoreParameterException) {
                throw ParameterNotFoundException::fromException($exception);
            }
        }

        array_walk_recursive($config, function (&$value) use ($bag) {
            try {
                $value = $bag->resolveValue($value);
            }
            catch (SymfonyParameterNotFoundException $exception) {
                if ( ! $this->ignoreParameterException) {
                    throw ParameterNotFoundException::fromException($exception);
                }
            }
            $value = $bag->unescapeValue($value);
        });

        $config[ 'parameters' ] = $bag->all();

        return $config;
    }

    private function resolveNestedParameters($values, string $prefix = ''): array
    {
        $convertedValues = [];
        foreach ($values as $key => $value) {
            // Do not provide numeric keys as single parameter
            if (is_numeric($key)) {
                continue;
            }

            $convertedValues[ $prefix . $key ] = $value;
            if (is_array($value)) {
                $convertedValues += $this->resolveNestedParameters($value, $prefix . $key . '.');
            }
        }

        return $convertedValues;
    }

    public function getParameterBag(): ParameterBag
    {
        $resolved = $this->resolveNestedParameters($this->parameters);
        $bag      = new ParameterBag($resolved);
        if ($this->model !== null) {
            $bag->setModel($this->model);
        }
        return $bag;//        return new ParameterBag($this->parameters->getItems());
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameter($key, $value)
    {
        data_set($this->parameters, $key, $value);
    }

    /**
     * @return \Codex\Contracts\Mergable\Model
     */
    public function getModel(): \Codex\Contracts\Mergable\Model
    {
        return $this->model;
    }

    /**
     * @param \Codex\Contracts\Mergable\Model $model
     */
    public function setModel(\Codex\Contracts\Mergable\Model $model)
    {
        $this->model = $model;
        return $this;
    }


}
