<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Codex\Documents\Processors\Links\Link;
use Codex\Documents\Processors\Links\Url;
use FluentDOM\DOM\Element;
use Illuminate\Foundation\Bus\DispatchesJobs;

class LinksProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    use DispatchesJobs;

    protected $defaultConfig = 'codex.processor-defaults.links';

    protected $depends = [ 'parser' ];

    public function getName()
    {
        return 'links';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $definition->add('replace_tag', 'mixed')->setDefault(false);
        $definition->add('prefix', 'string')->setDefault(null);
        $definition->add('actions', 'array.scalarPrototype');
    }

    public function process(Document $document)
    {
        if (false === $this->hasContent()) {
            return; // prevents ErrorException in Html.php line 42: DOMDocument::loadHTMLFile(): Empty string supplied as input
        }

        $d = $document->getDom();
        $d->find('//a')->each(function (Element $element) {
            /** @var Element $parent */
            $link = new Links\Link($this, $element);

            if ($link->isAction()) {
                $actions = $this->config('actions', []);

                // Check if link id is bound to a action/handler and set it to the link config before calling the handler
                if (\array_key_exists($link->getId(), $actions)) {
                    $action = $actions[ $link->getId() ];
                    $class  = $action;
                    $method = 'handle';
                    if (false !== \strpos($action, '@')) {
                        list($class, $method) = explode('@', $action);
                    }
                    $instance = app()->make($class);
                    app()->call([ $instance, $method ], [ 'link' => $link ]);
                }
            } elseif ($link->isInternal()) {
                $this->handleInternal($link);
            }
        });
        $document->setDom($d);
    }

    protected function handleInternal(Link $link)
    {
        $current     = Url::createFromString($this->document->url());
        $currentPath = $current->getPath();

        $path       = $link->getUrl()->getPath();
        $isDotted   = starts_with($path, '.');
        $isFilename = ends_with($path, $link->getAllowedExtensions(true));
        if ($isFilename) {
            $path = preg_replace('/\.\w+$/m', '', $path);
        }
        $path = str_ensure_left($path, '/../');
        if ($isDotted) {
            $path = str_ensure_left($path, '/../../');
        }

        $path = $currentPath . str_ensure_left($path, '/');
        $url  = (string)$current->withPath($path)->normalize();
        $link->setElementUrl($url);
    }

    /**
     * hasContent method.
     *
     * @return bool
     */
    protected function hasContent()
    {
        return \strlen(\trim($this->getDocument()->getContent())) > 0;
    }

    protected function getDocumentUrl(Links\Link $link)
    {
        // because the URL also includes the current page as segment, it means we have to go 1 higher. Example:
        // document: "codex/master/getting-started/configure"
        // has link: "../index"
        // normalizes to: "codex/master/getting-started/index"
        // this will fix that

        // endfix
        $currentRequestUrl = codex()->url($link->getProject()->getKey(), $link->getRevision()->getKey(), $link->getDocument()->getKey());
        $url               = $currentRequestUrl . str_ensure_left(path_without_extension($link->getUrl()->toString()), '/');

        return Url::createFromString($url)->normalize();
    }
}
