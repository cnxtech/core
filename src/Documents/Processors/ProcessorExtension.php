<?php

namespace Codex\Documents\Processors;

use Codex\Addons\Extensions\Extension;
use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionRegistry;
use Codex\Contracts\Documents\Document;
use Illuminate\Contracts\Config\Repository;

abstract class ProcessorExtension extends Extension
{
    protected $defaultConfig = null;

    protected $after = [];

    protected $before = [];

    /** @var Document */
    protected $document = null;

//    protected $provides = 'codex/core::processor.'

    abstract public function getName();

    abstract public function defineConfigAttributes(AttributeDefinition $definition);

    protected function registerDefaultConfig(Repository $config, AttributeDefinitionRegistry $registry)
    {
        $defaultConfig = $this->defaultConfig;
        if (is_string($defaultConfig)) {
            $defaultConfig = $config->get($defaultConfig, []);
        }
        $config->set('codex.processors.' . $this->getName(), $defaultConfig);
    }

    protected function registerConfigAttributes(Repository $config, AttributeDefinitionRegistry $registry)
    {
        $processors = $registry->getGroup('codex')->getChild('processors');
        $processor  = $processors->add($this->getName(), 'dictionary', 'Assoc');
        $this->defineConfigAttributes($processor);
    }

    public function onRegistered(Repository $config, AttributeDefinitionRegistry $registry)
    {
        $this->registerDefaultConfig($config, $registry);
        $this->registerConfigAttributes($config, $registry);
    }

    public function isEnabledForDocument(Document $document)
    {
        return in_array($this->getName(), $document[ 'processors.enabled' ], true);
    }

    public function config($key = null, $default = null, Document $document = null)
    {
        if ($document === null) {
            $document = $this->document;
        }
        $config = $document->attr($this->getConfigKey(), []);
        if ($key === null) {
            return $config;
        }
        return data_get($config, $key, $default);
    }

    protected function getConfigKey()
    {
        return "processors.{$this->getName()}";
    }

    public function getProvides()
    {
        return 'codex/core::processor.' . $this->getName();
    }

    public function getAfter()
    {
        return $this->after;
    }

    public function getBefore()
    {
        return $this->before;
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
     * @return \Codex\Contracts\Documents\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the document value
     *
     * @param \Codex\Contracts\Documents\Document|null $document
     *
     * @return ProcessorExtension
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }


}
