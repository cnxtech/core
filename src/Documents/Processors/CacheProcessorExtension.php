<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionRegistry;
use Codex\Contracts\Documents\Document;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;

class CacheProcessorExtension extends ProcessorExtension implements PreProcessorInterface, PostProcessorInterface
{
    protected $defaultConfig = 'codex.cache';

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    protected $after = [ 'attributes' ];

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getName()
    {
        return 'cache';
    }

    public function preProcess(Document $document)
    {
        if ($this->hasCachedContent()) {
            $document->setProcessed(true);
            $document->setProcessed(true, 'post');
            $document->setContentResolver(function (Document $document) {
                $prev = $this->getDocument();
                $this->setDocument($document);
                $content = $this->getCachedContent();
                $this->setDocument($prev);
                return $content;
            });
        }
    }

    public function postProcess(Document $document)
    {
        if ($this->shouldCache() && ! $this->hasCachedContent()) {
            $this->setCachedContent($document->getContent());
        }
    }

    protected function setCachedContent(string $content)
    {
        $document     = $this->getDocument();
        $minutes      = $this->config('minutes');
        $lastModified = $document->getLastModified();
        if ($minutes === null) {
            $this->cache->forever($this->getCacheKey(':last_modified'), $lastModified);
            $this->cache->forever($this->getCacheKey(':content'), $content);
        } else {
            $this->cache->put($this->getCacheKey(':last_modified'), $document->getLastModified(), $minutes);
            $this->cache->put($this->getCacheKey(':content'), $content, $minutes);
        }
    }

    protected function hasCachedContent(): bool
    {
        if ($this->shouldCache() && $this->getDocument()->getLastModified() === $this->getCachedLastModified()) {
            return $this->cache->has($this->getCacheKey(':content'));
        }
        return false;
    }

    protected function getCachedContent()
    {
        return $this->cache->get($this->getCacheKey(':content'));
    }

    /**
     * getCachedLastModified method.
     *
     * @return int
     */
    protected function getCachedLastModified()
    {
        if ( ! $this->shouldCache()) {
            return 0;
        }
        $minutes = $this->config('minutes');
        if (null === $minutes) {
            $lastModified = (int)$this->cache->rememberForever($this->getCacheKey(':last_modified'), function () {
                return 0;
            });
        } else {
            $lastModified = (int)$this->cache->remember($this->getCacheKey(':last_modified'), $minutes, function () {
                return 0;
            });
        }

        return $lastModified;
    }

    /**
     * shouldCache method.
     *
     * @return bool
     */
    protected function shouldCache()
    {
        return $this->config('enabled', false) === true;
    }

    /**
     * getCacheKey method.
     *
     * @param string $suffix
     *
     * @return string
     */
    protected function getCacheKey($suffix = '')
    {
        return $this->config('key') . '.document.' . $this->getDocument()->getProject()->getKey() . '.' . str_slug($this->getDocument()->getPath()) . $suffix;
    }

    protected function getConfigKey()
    {
        return 'cache';
    }

    public function onRegistered(Config $config, AttributeDefinitionRegistry $registry)
    {
//        $this->registerDefaultConfig($config, $registry);
//        $this->registerConfigAttributes($config, $registry);
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {

    }
}
