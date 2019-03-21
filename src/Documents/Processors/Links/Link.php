<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author    Robin Radic
 * @license   https://codex-project.mit-license.org MIT License
 */

namespace Codex\Documents\Processors\Links;

//use Codex\Concerns\HasConfig;
use Codex\Documents\Commands\AssignClassDocumentProperties;
use Codex\Documents\Processors\LinksProcessorExtension;
use ColinODell\Json5\SyntaxError;
use FluentDOM\DOM\Element;
use Illuminate\Foundation\Bus\DispatchesJobs;
use League\Uri\UriException;

/**
 * This is the class Action.
 *
 * @author         Robin Radic
 */
class Link
{
    use DispatchesJobs;
    use HasParameters;

    /** @var LinksProcessorExtension */
    protected $processor;

    /** @var Url */
    protected $url;

    /** @var Element */
    protected $element;

    /** @var array|Modifier[] */
    protected $modifiers = [];

    /** @var \Codex\Documents\Document */
    public $document;

    /** @var \Codex\Projects\Project */
    public $project;

    /** @var \Codex\Revisions\Revision */
    public $revision;

    protected $valid = false;

    /**
     * @var string
     */
    protected $id;

    /**
     * Action constructor.
     *
     * @param \Codex\Documents\Processors\LinksProcessorExtension $processor
     * @param \FluentDOM\DOM\Element                              $element
     *
     */
    public function __construct(LinksProcessorExtension $processor, Element $element)
    {
        $this->dispatch(new AssignClassDocumentProperties($processor->getDocument(), $this));
        $this->processor = $processor;
        $this->element   = $element;

        try {
            $this->url = Url::createFromString(urldecode($element->getAttribute('href')));
            $this->parse();
            $this->valid = true;
        }
        catch (UriException $exception) {

        }
    }

    /**
     * Parses the url/hash and assigns the id, parameters and modifiers to this instance.
     *
     * Example hashes:
     * <pre>#codex:document[2.1.1,getting-started/installation.md]:modal</pre>
     * <pre>index.md#codex:phpdoc:modifier:modifier[parameter,parameter]:modifier:modifier[parameter]</pre>
     */
    protected function parse()
    {
//        $result = preg_match('/codex:(.*?)(?:\[(.*?)\]|$|)(?:(:.*)|$)/', urldecode($this->url->toString()), $matches);
//        $result = preg_match('/codex:(.*?)(?:[\[(](.*?)[)\]]|$|)(?:(:.*)|$)/', urldecode($this->url->toString()), $matches);
        $result = preg_match('/codex:(.*?)(?:\[(.*?)\]|$|)(?:(:.*)|$)/', urldecode($this->url->toString()), $matches);
        if (0 === $result) {
            return;
        }
        @list($_, $id, $params, $modifiers) = $matches;
        $hasParams        = \strlen($params) > 0;
        $this->parameters = $hasParams ? $this->parseParameterString($params) : [];

        $hasModifiers = \strlen($modifiers) > 0;
        $modifiers    = $hasModifiers ? explode(':', str_remove_left($modifiers, ':')) : [];
        $modifiers    = array_map(function ($modifier) {
            $name   = $modifier;
            $params = [];
            if (stristr($modifier, '[')) {
                preg_match('/(.*?)\[(.*?)\]/', $modifier, $mparams);
                $name   = $mparams[ 1 ];
                $params = explode(',', $mparams[ 2 ]);
                $params = $this->parseParameterString($params);
            }

            return compact('name', 'params');
        }, $modifiers);
        foreach ($modifiers as $modifier) {
            $this->modifiers[ $modifier[ 'name' ] ] = new Modifier($modifier[ 'name' ], $modifier[ 'params' ]);
        }

        $this->id = $id;
    }


    protected function parseParameterString($params)
    {
        try {
            $params = preg_replace('/(?<!\\\)\\\(?!\\\)|(?<!\\\)\\\\\\\(?!\\\)/', '\\\\\\', $params);
            $params = json5_decode('[' . $params . ']', true, 512);
            return $params;
        }
        catch (SyntaxError $e) {
            $this->valid = false;
            return [];
        }
    }

    public function isValid()
    {
        return $this->valid;
    }

    /**
     * isAction method.
     *
     * @return bool
     */
    public function isAction()
    {
        return starts_with($this->getUrl()->getFragment(), $this->processor->config('prefix', 'codex') . ':');
    }

    public function isInternal()
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        $internal = $this->getUrl()->getHost() == null;
        $relative = ! starts_with($this->getUrl()->getPath(), '/');
        return $internal && $relative;
    }

    public function getAllowedExtensions($withDotPrefix = false)
    {
        if ( ! $withDotPrefix) {
            return $this->revision[ 'document_extensions' ];
        }
        return collect($this->revision[ 'document_extensions' ])->map(function ($extension) {
            return str_ensure_left($extension, '.');
        })->toArray();
    }

    public function isDocumentPath()
    {
        // no host = internal link
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($this->getUrl()->getHost() == null) {
            $path       = $this->getUrl()->getPath();
            $isRelative = ! starts_with($path, '/');
            $dotted     = starts_with($path, '..');
            if ($isRelative) {

            }
            if ($dotted) {
                $host       = str_replace_first('http://', '', url(''));
                $url        = Url::createFromString(url('documentation/codex/master/getting-started/installation'));
                $url        = $url->withPath($url->getPath() . str_ensure_left($path, '/../'));
                $normalized = $url->normalize();
            }
            if (str_contains($path, '.')) {
                $extensions = $this->revision[ 'document.extensions' ];
                $extension  = last(explode('.', $path));
                return in_array($extension, $extensions) && $isRelative;
            }
            $paths = $this->revision->getDocuments()->loadable();
            if ($paths->has($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * It will replace, If configured that <a> element should be be Vue's <router-link>.
     *
     * @return bool True if the element is replaced by router-link
     *
     * @throws \LogicException
     */
    public function checkFixRouterLink()
    {
        if ('router-link' !== $this->element->nodeName && config('codex.is_spa')) {
            $this->replaceElement('router-link', $this->element->textContent, [ 'to' => $this->url->toString() ]);
        }

        return config('codex.is_spa');
    }

    /**
     * replaceElement method.
     *
     * @param string $name
     * @param string $content
     * @param array  $attributes
     * @param bool   $copyAttributes
     *
     * @return Element
     *
     * @throws \LogicException
     */
    public function replaceElement(string $name, string $content = null, array $attributes = [], bool $copyAttributes = true)
    {
        $new = $this->element
            ->ownerDocument
            ->createElement($name, $content, $attributes);

        if ($copyAttributes && $this->element->hasAttributes()) {
            foreach ($this->element->attributes as $index => $attr) {
                /* @var \FluentDOM\DOM\Attribute $attr */
                $new->setAttribute($attr->name, $attr->value);
            }
        }
        $this->element->parentNode->insertBefore($new, $this->element);
        $this->element->remove();
        $this->element = $new;

        return $this->element;
    }

    public function replaceElementHtml($html)
    {
        $fd = FluentDOM($html, 'text/html-fragment');
        $this->element->before($fd->get());
        $new = $this->element->previousSibling;
        $this->element->remove();
        $this->element = $new;
        return $this->element;
    }

    /**
     * This will set the appropriate URL on the right attribute depending on <a href> or <router-link to>.
     *
     * @param \Codex\Documents\Processors\Links\Url|string $url
     */
    public function setElementUrl($url)
    {
        $isRouter = 'router-link' === $this->element->nodeName;
        $key      = $isRouter ? 'to' : 'href';
        $value    = $url;
        if ($url instanceof Url) {
            $value = $isRouter ? $url->getPath() : $url->toString();
        }

        $this->element->setAttribute($key, str_remove_left($value, url('/')));
        $this->url = Url::createFromString($value);
    }

    //region: Modifiers

    /**
     * hasModifiers method.
     *
     * @return bool
     */
    public function hasModifiers()
    {
        return ! empty($this->modifiers);
    }

    /**
     * countModifiers method.
     *
     * @return int
     */
    public function countModifiers(): int
    {
        return \count($this->modifiers);
    }

    /**
     * modifier method.
     *
     * @param string|int $modifier
     * @param mixed      $default
     *
     * @return \Codex\Documents\Processors\Links\Modifier|mixed|null
     */
    public function modifier($modifier, $default = null)
    {
        return $this->hasModifier($modifier) ? $this->modifiers[ $modifier ] : $default;
    }

    /**
     * hasModifier method.
     *
     * @param $i
     *
     * @return bool
     */
    public function hasModifier(string $str): bool
    {
        return array_key_exists($str, $this->modifiers);
    }

    /**
     * @return array|\Codex\Documents\Processors\Links\Modifier[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    //endregion

    //region: Getters & Setters

    /**
     * @return LinksProcessorExtension
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * getUrl method.
     *
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * setElement method
     *
     * @param $element
     *
     * @return $this
     */
    public function setElement($element)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * getDocument method
     *
     * @return \Codex\Documents\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * getProject method
     *
     * @return \Codex\Projects\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * getRevision method
     *
     * @return \Codex\Revisions\Revision
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * getId method.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    //endregion
}
