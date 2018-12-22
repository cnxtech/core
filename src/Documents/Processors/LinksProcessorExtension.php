<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Codex\Documents\Commands\AssignClassDocumentProperties;
use Codex\Documents\Processors\Links\Url;
use FluentDOM\DOM\Element;
use Illuminate\Foundation\Bus\DispatchesJobs;

class LinksProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    use DispatchesJobs;
//    protected $defaultConfig = 'codex.processors-defaults.links';

    protected $depends = [ 'parser' ];

    public function getName()
    {
        return 'links';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        // TODO: Implement defineConfigAttributes() method.
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

            $this->dispatch(new AssignClassDocumentProperties($this->document, $link));

            // Modify the url of the link if it points to a document
            // If vue router is used, replace the element with a <router-link>
            if ($link->isDocumentPath()) {
                $link->checkFixRouterLink();
                $link->setElementUrl($this->getDocumentUrl($link));
            }

            if ($link->isAction()) {
                $actions = array_merge(config('codex.processors.links.actions', []), $link->getDocument()->attr('processors.links.actions', []));

                // Check if link id is bound to a action/handler and set it to the link config before calling the handler
                if (\array_key_exists($link->getId(), $actions)) {
                    $action = $actions[$link->getId()];
                    $class = $action;
                    $method = 'handle';
                    if (false !== \strpos($action, '@')) {
                        list($class, $method) = explode('@', $action);
                    }
                    $instance = app()->make($class);
                    app()->call([$instance, $method], ['link' => $link]);
                }
            }
        });
        $document->setDom($d);
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
        $url = $currentRequestUrl.str_ensure_left(path_without_extension($link->getUrl()->toString()), '/');

        return Url::createFromString($url)->normalize();
    }
}
