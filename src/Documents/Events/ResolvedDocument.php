<?php

namespace Codex\Documents\Events;

use Codex\Contracts\Documents\Document;
use Illuminate\Foundation\Events\Dispatchable;

class ResolvedDocument
{
    use Dispatchable;

    /**
     * @var \Codex\Contracts\Documents\Document
     */
    protected $document;

    /**
     * Create a new event instance.
     *
     * @param \Codex\Contracts\Documents\Document $document
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * getDocument method
     *
     * @return \Codex\Contracts\Documents\Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
