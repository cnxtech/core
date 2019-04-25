<?php


namespace Codex\Attributes;


use Codex\Addons\Extensions\Extension;

abstract class AttributeExtension extends Extension
{
    protected $name;

    public function getName()
    {
        return $this->name;
    }

    public function getProvides()
    {
        return 'codex/core::attributes.' . $this->getName();
    }

    abstract public function register(AttributeDefinitionRegistry $registry);

    public function onRegistered(AttributeDefinitionRegistry $registry)
    {
        $this->register($registry);
    }
}
