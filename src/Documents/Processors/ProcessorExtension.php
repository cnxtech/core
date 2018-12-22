<?php

namespace Codex\Documents\Processors;

use Codex\Addons\Extensions\Extension;
use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionRegistry;
use Codex\Contracts\Documents\Document;
use Illuminate\Contracts\Config\Repository;
use Laradic\DependencySorter\Dependable;

abstract class ProcessorExtension extends Extension implements Dependable
{
    protected $defaultConfig = null;

    protected $depends = [];

    protected $pre = false; // resolved, read_content

//    protected $provides = 'codex/core::processor.'


    /** @var Document */
    protected $document = null;

    abstract public function getName();

    abstract public function process(Document $document);

    abstract public function defineConfigAttributes(AttributeDefinition $definition);

    public function handle(Document $document)
    {
        $this->document = $document;
        $this->process($document);
        $this->document = null;
    }

    public function onRegistered(Repository $config, AttributeDefinitionRegistry $registry)
    {
        $defaultConfig = $this->defaultConfig;
        if (is_string($defaultConfig)) {
            $defaultConfig = $config->get($defaultConfig, []);
        }
        $config->set('codex.processors.' . $this->getName(), $defaultConfig);

        $processors = $registry->getGroup('codex')->getChild('processors');
        $processor  = $processors->add($this->getName(), 'dictionary', 'Assoc');
        $this->defineConfigAttributes($processor);
    }

    public function isEnabledForDocument(Document $document)
    {
        return in_array($this->getName(), $document[ 'processors.enabled' ], true);
    }

    public function getProvides()
    {
        return 'codex/core::processor.' . $this->getName();
    }

    public function config($key = null, $default = null, Document $document = null)
    {
        if ($document === null) {
            $document = $this->document;
        }
        $config = $document->getAttribute("processors.{$this->getName()}");
        if ($key === null) {
            return $config;
        }
        return data_get($config, $key, $default);
    }

    public function getDependencies()
    {
        return $this->depends;
    }

    public function getHandle()
    {
        return $this->getName();
    }

    public function isPre()
    {
        return $this->pre;
    }

    /**
     * Set the pre value
     *
     * @param bool $pre
     *
     * @return ProcessorExtension
     */
    public function setPre(bool $pre)
    {
        $this->pre = $pre;
        return $this;
    }

    /**
     * @return null
     */
    public function getDefaultConfig()
    {
        return $this->defaultConfig;
    }

    /**
     * Set the config value
     *
     * @param null $defaultConfig
     *
     * @return ProcessorExtension
     */
    public function setDefaultConfig($defaultConfig)
    {
        $this->defaultConfig = $defaultConfig;
        return $this;
    }

    /**
     * Set the depends value
     *
     * @param array $depends
     *
     * @return ProcessorExtension
     */
    public function setDepends($depends)
    {
        $this->depends = $depends;
        return $this;
    }

}
