<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Illuminate\Contracts\Cache\Repository;

class CacheProcessorExtension extends ProcessorExtension implements PreProcessorInterface, PostProcessorInterface
{
    protected $defaultConfig = [
        'mode'    => null,
        'minutes' => null,
    ];

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    protected $depends = [ 'attributes' ];

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function getName()
    {
        return 'cache';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $definition->add('mode', 'string')->setDefault(null);
        $definition->add('minutes', 'mixed', 'Int')->setDefault(null);
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
        $mode = $this->config('mode');
        if ($mode === true || ($mode === null && config('app.debug', false) !== true)) {
            return true;
        }
        return false;
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
        return 'codex.document.' . $this->getDocument()->getProject()->getKey() . '.' . str_slug($this->getDocument()->getPath()) . $suffix;
    }
}
