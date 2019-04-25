<?php

namespace Codex\Documents;

use Codex\Concerns;
use Codex\Contracts\Documents\Document as DocumentContract;
use Codex\Contracts\Models\ChildInterface;
use Codex\Contracts\Revisions\Revision;
use Codex\Hooks;
use Codex\Models\Concerns\HasParent;
use Codex\Models\Model;
use Codex\Support\DB;

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
    use ProcessorTrait;

    const DEFAULTS_PATH = 'codex.documents';

    /** @var Revision */
    protected $parent;

    /**
     * @var string
     */
    protected $content;

    /** @var \Closure */
    protected $contentResolver;

    /** @var bool  */
    protected $rendered = false;


    /**
     * Document constructor.
     *
     * @param array    $attributes
     * @param Revision $revision
     */
    public function __construct(array $attributes, Revision $revision)
    {
        DB::startMeasure($revision->getProject() . ':' . $revision . ':' . $attributes[ 'key' ]);
        $this->setParent($revision);
        $this->setFiles($revision->getFiles());
        $this->contentResolver     = function (Document $document) {
            return $document->getFiles()->get($document->getPath());
        };
        $registry                  = $this->getCodex()->getRegistry()->resolve('documents');
        $attributes[ 'extension' ] = path_get_extension($attributes[ 'path' ]);
        $attributes                = Hooks::waterfall('document.initialize', $attributes, [ $registry, $this ]);
        $this->initialize($attributes, $registry);
        $this->addGetMutator('inherits', 'getInheritKeys', true, true);
        $this->addGetMutator('changes', 'getChanges', true, true);
        $this->addGetMutator('content', 'render', true, true);
        $this->addGetMutator('last_modified', 'getLastModified', true, true);
        Hooks::run('document.initialized', [ $this ]);
        DB::stopMeasure($revision->getProject());
        DB::stopMeasure($revision->getProject() . ':' . $revision);
        DB::stopMeasure('revision:' . $this->getProject()->getKey() . ':' . $this->getRevision()->getKey());
    }

    public function url($absolute = true)
    {
        return $this->getRevision()->url($this->getKey(), $absolute);
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

    function getProcessHookPrefix(): string
    {
        return 'document.';
    }

    public function render($skipProcessing = false, $force = false)
    {
        if ( ! $force && $this->isRendered()) {
            return $this->getContent();
        }

        if ( ! $skipProcessing) {
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
        if ( ! $skipProcessing) {
            $this->process();

            $this->postprocess();
        }

        return $this->getContent();
    }

    public function isRendered()
    {
        return $this->rendered;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
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

    /** @return DOMQueryDecorator */
    public function getDOM()
    {
        $content                              = $this->getContent();
        $query                                = \FluentDOM::QueryCss($content, 'html', [
            \FluentDOM\Loader\Options::ENCODING       => 'UTF-8',
            \FluentDOM\Loader\Options::FORCE_ENCODING => false,
        ]);
        $query->document->strictErrorChecking = false;
        return new DOMQueryDecorator($query, $this);
    }

    /** @param \FluentDOM\Query $dom */
    public function saveDOM($dom)
    {
        $this->setContent(urldecode($dom->find('body')->html()));
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
