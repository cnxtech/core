<?php

namespace Codex\Projects\Events;

use Codex\Contracts\Projects\Project;
use Illuminate\Foundation\Events\Dispatchable;

class ResolvedProject
{
    use Dispatchable;

    /**
     * @var \Codex\Contracts\Projects\Project
     */
    protected $project;

    /**
     * Create a new event instance.
     *
     * @param \Codex\Contracts\Projects\Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * getProject method
     *
     * @return \Codex\Contracts\Projects\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
