<?php


namespace Codex\Concerns;


use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;

/**
 * This is the HasContainer trait.
 *
 * @package Codex\Concerns
 * @author  Robin Radic
 */
trait HasContainer
{
    /**
     * getContainer method
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function getContainer()
    {
        return Container::getInstance();
    }

    /**
     * make method
     *
     * @param       $abstract
     * @param array $parameters
     *
     * @return mixed
     */
    protected function make($abstract, array $parameters = [])
    {
        return $this->getContainer()->make($abstract, $parameters);
    }
}
