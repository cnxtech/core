<?php

namespace Codex\Documents;

use Codex\Documents\Commands\FindDocuments;
use Codex\Documents\Commands\MakeDocument;
use Codex\Mergable\ModelCollection;

/**
 * This is the class DocumentCollection.
 *
 * @package Codex\Documents
 * @author  Robin Radic
 * @method \Codex\Contracts\Documents\Document get($key)
 */
class DocumentCollection extends ModelCollection
{
    /**
     * getRevision method
     *
     * @return \Codex\Contracts\Revisions\Revision
     */
    public function getRevision()
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
        return $this->dispatch(new FindDocuments($this->getRevision()));
    }

    /**
     * resolveModels method
     *
     * @return mixed
     */
    protected function makeModel($key)
    {
        $model = $this->dispatch(new MakeDocument($this->getRevision(), $this->getLoadable($key)));

        return $model;
    }

    /**
     * getDefault method
     *
     * @return mixed
     */
    public function getDefaultKey()
    {
        return $this->getRevision()->document[ 'default' ];
    }
}
