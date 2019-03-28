<?php

namespace Codex\Mergable;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException as SymfonyParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Zend\ConfigAggregatorParameters\ParameterNotFoundException;

class ParameterPostProcessor
{
    /**
     * @var array
     */
    private $parameters;

    /** @var bool */
    private $ignoreParameterException;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters, bool $ignoreParameterException = false)
    {
        $this->parameters               = $parameters;
        $this->ignoreParameterException = $ignoreParameterException;
    }


    public function __invoke(array $config): array
    {

        try {
            $parameters = $this->getResolvedParameters();
        }
        catch (SymfonyParameterNotFoundException $exception) {
            if ( ! $this->ignoreParameterException) {
                throw ParameterNotFoundException::fromException($exception);
            }
            $parameters = new ParameterBag($config);
        }

        array_walk_recursive($config, function (&$value) use ($parameters) {
            try {
                $value      = $parameters->resolveValue($value);
            }
            catch (SymfonyParameterNotFoundException $exception) {
                if ( ! $this->ignoreParameterException) {
                    throw ParameterNotFoundException::fromException($exception);
                }
            }
            $value = $parameters->unescapeValue($value);
        });

        $config[ 'parameters' ] = $parameters->all();

        return $config;
    }

    private function resolveNestedParameters(array $values, string $prefix = ''): array
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

    private function getResolvedParameters(): ParameterBag
    {
        $resolved = $this->resolveNestedParameters($this->parameters);
        $bag      = new ParameterBag($resolved);

        $bag->resolve();
        return $bag;
    }

}
