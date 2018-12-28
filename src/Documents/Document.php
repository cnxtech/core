<?php

namespace Codex\Documents;

use Codex\Concerns;
use Codex\Contracts\Documents\Document as DocumentContract;
use Codex\Contracts\Mergable\ChildInterface;
use Codex\Contracts\Revisions\Revision;
use Codex\Mergable\Concerns\HasParent;
use Codex\Mergable\Model;

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
    protected $processed = false;

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
        $attributes[ 'extension' ] = path_get_extension($attributes[ 'path' ]);
        $this->init($attributes, $registry);
        $this->addGetMutator('content', 'getContent', true, true);
        $this->addGetMutator('last_modified', 'getLastModified', true, true);
        $this->addGetMutator('attributes', 'getAttributes', true, true);
        $this->addGetMutator('changed', 'getChanged', true, true);
        $this->addGetMutator('changes', 'getChanges', true, true);
    }

    public function url()
    {
        return $this->getRevision()->url($this->getKey());
    }

    /**
     * getRevision method
     *
     * @return Revision
     */
    public function getRevision()
    {
        return $this->getParent();
    }

    /** @return \Codex\Contracts\Projects\Project */
    public function getProject()
    {
        return $this->getRevision()->getProject();
    }

    public function getLastModified()
    {
        return $this->getFiles()->lastModified($this->getPath());
    }

    //region: Content methods and processing

    public function getContent($triggerProcessing = true)
    {
        if ($triggerProcessing) {
            $this->preprocess();
        }
        // resolve the content and postprocess it without caching
        if ($this->content === null) {
            $resolver      = $this->contentResolver;
            $this->content = $resolver($this);
        }
        // @todo: find a better way to fix this, This way the  FluentDOM::Query('', 'text/html') does not generate a exception
        if ('' === $this->content) {
            $this->content = ' ';
        }
        if ($triggerProcessing) {
            $this->process();

            $this->postprocess();
        }
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

    /** @return DOMQueryDecorator */
    public function getDOM()
    {
        // uses https://github.com/ThomasWeinert/PhpCss for css queries
        $content                              = $this->getContent();
        $query                                = \FluentDOM::QueryCss($content, 'html', [
            \FluentDOM\Loader\Options::ENCODING       => 'UTF-8',
            \FluentDOM\Loader\Options::FORCE_ENCODING => false,
//            \FluentDOM\Loader\Xml::LIBXML_OPTIONS => LIBXML_ERR_NONE | LIBXML_NOERROR | LIBXML_HTML_NODEFDTD
        ]);
        $query->document->strictErrorChecking = false;
        return new DOMQueryDecorator($query, $this);
        // https://stackoverflow.com/questions/23426745/case-sensitivity-with-getelementbytagname-and-getattribute-php
//        return FluentDOM::Query(, 'text/html'); //, [FluentDOM\Loader\Xml::LIBXML_OPTIONS => LIBXML_ERR_NONE | LIBXML_NOERROR]);
    }

    /** @param \FluentDOM\Query $dom */
    public function saveDOM($dom)
    {
        $this->content = urldecode($dom->find('body')->html());
//        $this->content = $dom->find('//body')->first()->html();
    }

    public function setProcessed(bool $value, string $type = null)
    {
        if ($type !== null && ! in_array($type, [ 'pre', 'post' ], true)) {
            throw new \Exception("Invalid process type [{$type}]. Should be either 'pre' or 'post'");
        }
        $propertyName        = camel_case(($type === null ? '' : $type) . '_Processed');
        $this->$propertyName = $value;
        return $this;
    }

    protected function triggerProcess(string $type = null)
    {
        if ($type !== null && ! in_array($type, [ 'pre', 'post' ], true)) {
            throw new \Exception("Invalid process type [{$type}]. Should be either 'pre' or 'post'");
        }
        $propertyName = camel_case(($type === null ? '' : $type) . '_Processed');
        if ($this->$propertyName) {
            return $this;
        }
        $triggerName = ($type === null ? '' : "{$type}_") . 'process';

        $this->$propertyName = true;
        $this->fire($triggerName, [ 'document' => $this ]);
        return $this;
    }

    public function process()
    {
        return $this->triggerProcess();
    }

    public function preprocess()
    {
        return $this->triggerProcess('pre');
    }

    public function postprocess()
    {
        return $this->triggerProcess('post');
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

    //endregion


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

}
