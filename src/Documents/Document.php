<?php

namespace Codex\Documents;

use Codex\Concerns;
use Codex\Contracts\Documents\Document as DocumentContract;
use Codex\Contracts\Mergable\ChildInterface;
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

    /** @var Revision */
    protected $parent;

    /**
     * @var string
     */
    protected $content;

    /** @var \Closure */
    protected $contentResolver;

    /** @var bool */
    protected $preProcessed = false;

    /** @var bool */
    protected $postProcessed = false;

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
        $this->contentResolver = function (Document $document) {
            return $document->getFiles()->get($document->getPath());
        };
        $registry              = $this->getCodex()->getRegistry()->resolveGroup('documents');
        $registry->add($this->primaryKey, $this->keyType)->setApiType('ID!');
        $attributes[ 'extension' ] = path_get_extension($attributes[ 'path' ]);
        $this->init($attributes, $registry);
        $this->addGetMutator('content', 'getContent', true, true);
        $this->addGetMutator('last_modified', 'getLastModified', true, true);
        $this->addGetMutator('attributes', 'getAttributes', true, true);
        $registry->add('extension', 'string');
        $registry->add('content', 'string');
        $registry->add('last_modified', 'integer');
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
        return new DocumentCollection($models, $this->getParent());
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
            $this->content = $this->contentResolver->call($this, $this);
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


    protected function process(string $type)
    {
        if ( ! in_array($type, [ 'pre', 'post' ], true)) {
            throw new \Exception("Invalid process type [{$type}]. Should be either 'pre' or 'post'");
        }
        $propertyName = $type . 'Processed';
        if ($this->$propertyName) {
            return $this;
        }

        $this->$propertyName = true;
        $this->fire($type . '_process', [ 'document' => $this ]);
        return $this;
    }

    public function preprocess()
    {
        return $this->process('pre');
    }

    public function postprocess()
    {
        return $this->process('post');
    }


    /**
     * @return \Closure
     */
    public function getContentResolver()
    {
        return $this->contentResolver;
    }

    /**
     * Set the contentResolver value
     *
     * @param \Closure $contentResolver
     *
     * @return Document
     */
    public function setContentResolver($contentResolver)
    {
        $this->contentResolver = $contentResolver;
        return $this;
    }

}
