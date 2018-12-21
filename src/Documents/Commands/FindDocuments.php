<?php

namespace Codex\Documents\Commands;

use Codex\Contracts\Revisions\Revision;
use Codex\Documents\Document;

/**
 * This is the class FindDocumentFiles.
 *
 * @package Codex\Documents\Commands
 * @author  Robin Radic
 */
class FindDocuments
{
    /**
     * @var \Codex\Contracts\Revisions\Revision
     */
    protected $revision;

    /**
     * FindDocumentFiles constructor.
     *
     * @param \Codex\Contracts\Revisions\Revision $revision
     */
    public function __construct(Revision $revision)
    {
        $this->revision = $revision;
    }

    /**
     * handle method
     *
     * @return mixed
     */
    public function handle()
    {
        $revision  = $this->revision;
        $fs        = $revision->getFiles();
        $filePaths = $fs->allFiles($revision->getKey());

        $paths = collect($filePaths)
            ->map(function ($path) { // convert all paths to pathinfo arrays
                $pathInfo = pathinfo($path);
                $key      = Document::getKeyFromPath($path);
                return array_merge($pathInfo, compact('path', 'key'));
            })
            ->filter(function ($pathInfo) { // filter not allowed extensions
                return in_array($pathInfo[ 'extension' ], $this->revision[ 'document' ][ 'extensions' ]);
            })
            ->keyBy('key')
            ->transform(function ($pathInfo) {
                return $pathInfo[ 'path' ];
            });


        return $paths->toArray();
    }

}
