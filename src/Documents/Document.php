<?php

namespace Codex\Documents;

use Codex\Concerns;
use Codex\Contracts\Documents\Document as DocumentContract;
use Codex\Contracts\Mergable\ChildInterface;
use Codex\Contracts\Mergable\Mergable;
use Codex\Contracts\Revisions\Revision;
use Codex\Mergable\Concerns\HasParent;
use Codex\Mergable\Model;
use FluentDOM;

/**
 * This is the class Document.
 *
 * @package Codex\Documents
 * @author  Robin Radic
 * @method Revision getParent()
 * @method string getExtension()
 * @method string getKey()
 * @method string getPath()
 */
class Document extends Model implements DocumentContract, ChildInterface
{
    use Concerns\HasFiles;
    use Concerns\HasCodex;
    use HasParent {
        _setParentAsProperty as setParent;
    }

    const DEFAULTS_PATH = 'codex.documents';
    /** @var array */
//    public $mergePaths = [
//        Mergable::CASTS_PATH    => 'codex.documents.casts',
//        Mergable::DEFAULTS_PATH => 'codex.documents.defaults',
//        Mergable::INHERITS_PATH => 'codex.documents.inherits',
//    ];

    /** @var Revision */
    protected $parent;

    /**
     * @var string
     */
    protected $content;

    /**
     * Document constructor.
     *
     * @param array    $attributes
     * @param Revision $revision
     */
    public function __construct(array $attributes, Revision $revision)
    {
        $this->setParent($revision);
        $this->setFiles($revision->getFiles());
        $definitions = $this->getCodex()->getRegistry()->resolveGroup('documents');
        $attributes[ 'extension' ] = path_get_extension($attributes[ 'path' ]);
        $this->init($attributes, $definitions);
        $this->addGetMutator('content', 'getContent', true, true);
        $this->addGetMutator('last_modified', 'getLastModified', true, true);
        $this->addGetMutator('attributes', 'getAttributes', true, true);
        $definitions->add('extension', 'string');
        $definitions->add('content', 'string');
        $definitions->add('last_modified', 'integer');
    }

    /**
     * getRevision method
     *
     * @return mixed
     */
    public function getRevision()
    {
        return $this->getParent();
    }

    /**
     * newCollection method
     *
     * @param array $models
     *
     * @return \Codex\Documents\DocumentCollection|\Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new DocumentCollection($this->getParent(), $models);
    }

    /**
     * getKeyFromPath method
     *
     * @param $path
     *
     * @return string
     */
    public static function getKeyFromPath($path)
    {
        return implode('/', \array_slice(explode('/', path_without_extension($path)), 1));
    }

    public function getLastModified()
    {
        return $this->getFiles()->lastModified($this->getPath());
    }

    public function getContent()
    {
        $this->preprocess();

        if ($this->content === null) {
            $this->content = $this->getFiles()->get($this->getPath());
            if ('' === $this->content) {
                $this->content = ' ';
            }
        }

        // @todo: find a better way to fix this, This way the  FluentDOM::Query('', 'text/html') does not generate a exception
        if ('' === $this->content) {
            $this->content = ' ';
        }

        $this->postprocess();
        return $this->content;
    }

    /**
     * setContent method
     *
     * @param $content
     *
     * @return $this|\Codex\Contracts\Documents\Document
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }


    public function getDom(): \FluentDOM\Query
    {
        // https://stackoverflow.com/questions/23426745/case-sensitivity-with-getelementbytagname-and-getattribute-php
        return FluentDOM::Query($this->getContent(), 'text/html'); //, [FluentDOM\Loader\Xml::LIBXML_OPTIONS => LIBXML_ERR_NONE | LIBXML_NOERROR]);
    }

    /** @param \FluentDOM\Query $dom */
    public function setDom($dom)
    {
        $this->content = $dom->find('//body')->first()->html();
    }

    protected $preProcessed = false;

    protected $postProcessed = false;

    public function preprocess()
    {
        if ($this->preProcessed) {
            return $this;
        }

        $this->preProcessed = true;
        $this->fire('pre_process', [ 'document' => $this ]);
        return $this;
    }

    public function postprocess()
    {
        if ($this->postProcessed) {
            return $this;
        }

        $this->postProcessed = true;
        $this->fire('post_process', [ 'document' => $this ]);
        return $this;
    }

}
