<?php

namespace Codex\Tests\Fixture;

use Mockery as m;

trait CreatesMainMocks
{
    /** @var \Mockery\MockInterface */
    protected $config;

    /** @var \Mockery\MockInterface */
    protected $projects;

    /** @var \Mockery\MockInterface */
    protected $project;

    /** @var \Mockery\MockInterface */
    protected $document;

    /** @var \Mockery\MockInterface */
    protected $documents;

    /** @var \Mockery\MockInterface */
    protected $revision;

    /** @var \Mockery\MockInterface */
    protected $revisions;

    protected function createMainMocks()
    {
        $this->config   = m::mock('Illuminate\Contracts\Config\Repository');
        $this->projects = m::mock('Codex\Projects\ProjectCollection');
        $this->project  = m::mock('Codex\Contracts\Projects\Project');

        $this->revisions = m::mock('Codex\Revisions\RevisionCollection');
        $this->revision  = m::mock('Codex\Contracts\Revisions\Revision');

        $this->documents = m::mock('Codex\Documents\DocumentCollection');
        $this->document  = m::mock('Codex\Contracts\Documents\Document');
    }

}
