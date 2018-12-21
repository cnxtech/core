<?php

namespace Codex\Revisions\Commands;

use Codex\Contracts\Projects\Project;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * This is the class FindRevisionConfigs.
 *
 * @package Codex\Revisions\Commands
 * @author  Robin Radic
 */
class FindRevisions
{
    use DispatchesJobs;

    /**
     * @var \Codex\Contracts\Projects\Project
     */
    protected $project;

    /**
     * FindRevisionConfigs constructor.
     *
     * @param \Codex\Contracts\Projects\Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * handle method
     *
     * @return array
     */
    public function handle()
    {
        $configFilePaths = [];
        foreach ($this->getFS()->directories() as $directory) {
            if ($configFilePath = $this->getConfigFileFrom($directory)) {
                $configFilePaths[ $directory ] = $configFilePath;
            }
        }
        return $configFilePaths;
    }

    /**
     * getConfigFileFrom method
     *
     * @param $directory
     *
     * @return bool|string
     */
    protected function getConfigFileFrom($directory)
    {
        foreach ($this->getAllowedNames() as $name) {
            $path = $directory . '/' . $name;
            if ($this->getFS()->exists($path)) {
                return $path;
            }
        }
        return false;
    }

    /**
     * getFS method
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function getFS()
    {
        return $this->project->getFiles();
    }

    /**
     * getAllowedNames method
     *
     * @return array
     */
    protected function getAllowedNames()
    {
        return array_filter($this->project[ 'revision.allowed_config_files' ], function ($fileName) {
            if ($this->project[ 'revision.allow_php_config' ] !== true && ends_with($fileName, '.php')) {
                return false;
            }
            return true;
        });
    }

}
