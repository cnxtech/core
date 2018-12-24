<?php

namespace Codex\Revisions\Commands;

use Codex\Contracts\Projects\Project;
use Codex\Mergable\Commands\MergeAttributes;
use Codex\Revisions\Events\ResolvedRevision;
use Codex\Revisions\Revision;
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

    /**
     * handle method
     *
     * @param \Illuminate\Filesystem\Filesystem $localFS
     *
     * @return \Codex\Contracts\Revisions\Revision|\Illuminate\Foundation\Application|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $path                = $this->configFilePath;
        $attributes          = $this->getAttributes();
        $attributes[ 'key' ] = basename(dirname($path));
        $project             = $this->project;
        $revision            = app()->make(Revision::class, compact('attributes'));
        $revision->setParent($project);
        $revision->setFiles($project->getFiles());
        $this->dispatch(new MergeAttributes($revision));
        $revision->fireEvent('resolved', $revision);
        ResolvedRevision::dispatch($revision);
        return $revision;
    }

    /**
     * getAttributes method
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
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
}
