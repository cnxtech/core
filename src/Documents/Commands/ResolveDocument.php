<?php

namespace Codex\Documents\Commands;

use Codex\Contracts\Documents\Document as DocumentContract;
use Codex\Contracts\Revisions\Revision;
use Codex\Documents\Document;
use Codex\Documents\Events\ResolvedDocument;
use Codex\Hooks;
use Codex\Mergable\Commands\AggregateAttributes;
use Codex\Mergable\Commands\MergeAttributes;
use Codex\Mergable\Concerns\BuildsParameterData;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ResolveDocument
{
    use DispatchesJobs;
    use BuildsParameterData;

    /**
     * @var \Codex\Contracts\Revisions\Revision
     */
    protected $revision;

    protected $documentPath;

    /**
     * FindDocumentFiles constructor.
     *
     * @param \Codex\Contracts\Revisions\Revision $revision
     */
    public function __construct(Revision $revision, $documentPath)
    {
        $this->revision     = $revision;
        $this->documentPath = $documentPath;
    }

    /**
     * handle method
     *
     * @return \Codex\Contracts\Documents\Document
     */
    public function handle()
    {
        $revision   = $this->revision;
        $path       = $this->documentPath;
        $key        = Document::getKeyFromPath($path);
        $attributes = compact('key', 'path');
        $document   = app(DocumentContract::class, compact('attributes', 'revision'));
        $this->dispatch(new MergeAttributes($document));


        $parameters = $this->buildParameterData($document);
        $attributes = $document->getAttributes();
        $attributes = $this->dispatch(new AggregateAttributes($attributes, $parameters, true, false));
        $document->setRawAttributes($attributes);
        Hooks::run('document.resolved', [ $document ]);
        $document->fireEvent('resolved');
        ResolvedDocument::dispatch($document);
        $document->preprocess();
        $document->fire('resolved', [ 'document' => $document ]);
        return $document;
    }


}
