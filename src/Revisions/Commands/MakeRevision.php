<?php

namespace Codex\Revisions\Commands;

use Codex\Contracts\Projects\Project;
use Codex\Mergable\Commands\MergeAttributes;
use Codex\Revisions\Events\ResolvedRevision;
use Codex\Revisions\Revision;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Yaml\Yaml;

class MakeRevision
{
    use DispatchesJobs;

    /**
     * @var string
     */
    protected $configFilePath;

    /** @var \Codex\Contracts\Projects\Project */
    protected $project;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $localFS;

    /** @var \Codex\Contracts\Revisions\Revision */
    protected $revision;

    /**
     * MakeProject constructor.
     *
     * @param string $configFilePath
     */
    public function __construct(Project $project, string $configFilePath)
    {
        $this->configFilePath = $configFilePath;
        $this->project        = $project;
    }

    public function handle(Repository $cache)
    {
        $project             = $this->project;
        $revision            = $this->makeRevision();

        if ($revision->attr('cache.enabled', $project->attr('cache.enabled'))) {
//        $cache->forget($this->cacheKey('projectLastModified'));
//        $cache->forget($this->cacheKey('lastModified'));
//        $cache->forget($this->cacheKey('data'));
            $projectLastModified = $project->getLastModified();
            if ($projectLastModified > $cache->get($this->cacheKey('projectLastModified'), 0)) {
                $cache->forget($this->cacheKey('data'));
            }

            $lastModified = $revision->getLastModified();
            if ($lastModified > $cache->get($this->cacheKey('lastModified'), 0)) {
                $cache->forget($this->cacheKey('data'));
            }

            $cache->put($this->cacheKey('projectLastModified'), $projectLastModified, 5);
            $cache->put($this->cacheKey('lastModified'), $lastModified, 5);
            $attr = $cache->remember($this->cacheKey('data'), 5, function (...$args) use ($revision) {
                $this->dispatch(new MergeAttributes($revision));
                return $revision->getAttributes();
            });
            $revision->setRawAttributes($attr);
        } else {
            $this->dispatch(new MergeAttributes($revision));
        }

        $revision->fireEvent('resolved', $revision);
        ResolvedRevision::dispatch($revision);
        return $revision;
    }

    protected function makeRevision()
    {
        $path                = $this->configFilePath;
        $attributes          = $this->getAttributes();
        $attributes[ 'key' ] = basename(dirname($path));
        $project             = $this->project;
        $this->revision      = app()
            ->make(Revision::class, compact('attributes'))
            ->setParent($project)
            ->setFiles($project->getFiles())
            ->setConfigFilePath($path);
        return $this->revision;
    }

    protected function getAttributes()
    {
        $content = $this->project->getFiles()->get($this->configFilePath);
        $isPhp   = ends_with($this->configFilePath, '.php');
        if ($isPhp) {
            $localFS     = app(Filesystem::class);
            $tmpFilePath = storage_path(str_random() . '.php');
            $localFS->put($tmpFilePath, $content);
            $attributes = require $tmpFilePath;
            $localFS->delete($tmpFilePath);
            return $attributes;
        }
        return Yaml::parse($content);
    }

    public function cacheKey($suffix = '')
    {
        return $this->revision->attr('cache.key', $this->project->attr('cache.key')) . '.revision.attributes.' . $this->project->getKey() . $this->configFilePath . $suffix;
    }

}
