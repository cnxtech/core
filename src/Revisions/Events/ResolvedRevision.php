<?php

namespace Codex\Revisions\Events;

use Codex\Contracts\Revisions\Revision;
use Illuminate\Foundation\Events\Dispatchable;

class ResolvedRevision
{
    use Dispatchable;

    /**
     * @var \Codex\Contracts\Revisions\Revision
     */
    protected $revision;

    /**
     * Create a new event instance.
     *
     * @param \Codex\Contracts\Revisions\Revision $revision
     */
    public function __construct(Revision $revision)
    {
        $this->revision = $revision;
    }

    /**
     * getRevision method
     *
     * @return \Codex\Contracts\Revisions\Revision
     */
    public function getRevision()
    {
        return $this->revision;
    }
}
