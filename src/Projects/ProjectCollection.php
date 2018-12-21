<?php

namespace Codex\Projects;

use Codex\Mergable\ModelCollection;
use Codex\Projects\Commands\FindProjects;
use Codex\Projects\Commands\MakeProject;
use Codex\Projects\Events\ResolvedProject;

/**
 * This is the class ProjectCollection.
 *
 * @package Codex\Projects
 * @author  Robin Radic
 * @method \Codex\Projects\Project get($key)
 */
class ProjectCollection extends ModelCollection
{
    /**
     * getCodex method
     *
     * @return mixed
     */
    public function getCodex()
    {
        return $this->getParent();
    }

    /**
     * resolveModels method
     *
     * @return array
     */
    protected function resolveLoadable()
    {
        return $this->dispatch(new FindProjects());
    }

    /**
     * resolveModels method
     *
     * @return \Codex\Contracts\Projects\Project
     */
    protected function makeModel($key)
    {
        return $this->dispatch(new MakeProject($this->getLoadable($key)));
    }

    /**
     * getDefault method
     *
     * @return mixed
     */
    public function getDefaultKey()
    {
        return $this->getCodex()->default_project;
    }
}
