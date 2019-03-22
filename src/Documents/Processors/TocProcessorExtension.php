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

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Codex\Processors\Toc\Header;
use Illuminate\Contracts\View\Factory;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * This is the class TocFilter.
 *
 * @author         CLI
 * @copyright      Copyright (c) 2015, CLI. All rights reserved
 *
 * @Processor("toc", priority=50, config="config", after={"parser"})
 */
class TocProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    /** @var \Codex\Codex */
    public $codex;

    protected $defaultConfig = 'codex.processor-defaults.toc';

    protected $after = [];

    /** @var \Illuminate\Contracts\View\Factory */
    protected $view;

    protected $slugs = [];

    protected $nodes = [];

    /**
     * TocFilter constructor.
     *
     * @param $view
     */
    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    public function getName()
    {
        return 'toc';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $definition->add('disable', 'array.scalarPrototype', '[Int]!');           //=> [ 1 ],
        $definition->add('regex', 'string')->setDefault('/<h(\d)>([\w\W]*?)<\/h\d>/');
        $definition->add('header_link_show', 'boolean')->setDefault(false);
        $definition->add('header_link_text', 'string')->setDefault('#');
        $definition->add('minimum_nodes', 'integer')->setDefault(2);
        $definition->add('header_view', 'string')->setDefault('codex::processors.toc-header');
        $definition->add('view', 'string')->setDefault('codex::processors.toc');
    }

    public function proces3s(Document $document)
    {
        $content = $document->getContent();
        $dom      = $document->getDOM();
        $prevSize = 0;
        $prevNode = $rootNode = $this->createHeaderNode(0, 'root');
        $elements = $dom->query('h1, h2, h3, h4, h5, h6');
        foreach ($elements as $h => $element) {
            $original = $element->saveHtml();
            $size     = (int)preg_replace('/^.*(\d).*$/m', '$1', $element->tagName);
            $text     = $element->nodeValue;
            if (\in_array($size, $this->config('disable'), true)) {
                continue;
            }
            $node = $this->createHeaderNode($size, $text);
            if ($size === $prevSize) {
                $prevNode->getParent()->addChild($node);
                $node->setParent($prevNode->getParent());
            } elseif ($size < $prevSize) {
                $parentNode = $prevNode->getParent();
                while (true) {
                    if ($size === $parentNode->getValue()->getSize()) {
                        $parentNode->getParent()->addChild($node);
                        $node->setParent($parentNode->getParent());
                        break;
                    }
                    if ($parentNode === $rootNode) {
                        break;
                    }
                    $parentNode = $parentNode->getParent();
                }
            } elseif ($size > $prevSize) {
                $prevNode->addChild($node);
                $node->setParent($prevNode);
            }

            $node->getValue()->setSlug($slug = $this->makeSlug($text));
            $replacement = $this->view
                ->make($this->config('header_view'), $this->config())
                ->with(compact('text', 'size', 'slug'))
                ->render();
            $content     = str_replace($original, $replacement, $content);

            $prevSize = $size;
            $prevNode = $node;
        }
        if (\count($this->nodes) >= (int)$this->config('minimum_nodes')) {
            $toc = $this->view
                ->make($this->config('view'), $this->config())
                ->with('items', $rootNode->getChildren())
                ->render();
            $document->setContent($toc . $content);
        }

//        $dom->saveToDocument();
    }

    public function process(Document $document)
    {
        $content = $document->getContent();
        $total   = preg_match_all($this->config('regex'), $content, $matches);
//         create root
//         for each header
//         create node
//         if header nr is same as previous, assign to same parent as previous
//         if header nr is lower then previous, check parent header nr, if header nr lower then parent nr, check parent, etc
//         if header nr is higher then previous, assign as previous child

        // Generate TOC Tree from HTML
        $prevSize = 0;
        $prevNode = $rootNode = $this->createHeaderNode(0, 'root');
        for ($h = 0; $h < $total; ++$h) {
            $original = $matches[ 0 ][ $h ];
            $size     = (int)$matches[ 1 ][ $h ];
            $text     = $matches[ 2 ][ $h ];
            if (\in_array($size, $this->config('disable'), true)) {
                continue;
            }
            $node = $this->createHeaderNode($size, $text);
            if ($size === $prevSize) {
                try {
                    $prevNode->getParent()->addChild($node);
                } catch (\Throwable $e){
                    //@todo fix this
                    return;
                }
                $node->setParent($prevNode->getParent());
            } elseif ($size < $prevSize) {
                $parentNode = $prevNode->getParent();
                while (true) {
                    if ($size === $parentNode->getValue()->getSize()) {
                        if(!$parentNode->getParent()){
                            $rootNode->addChild($node);
                            break;
                        }
                        $parentNode->getParent()->addChild($node);
                        $node->setParent($parentNode->getParent());
                        break;
                    }
                    if ($parentNode === $rootNode) {
                        break;
                    }
                    $parentNode = $parentNode->getParent();
                }
            } elseif ($size > $prevSize) {
                $prevNode->addChild($node);
                $node->setParent($prevNode);
            }

            $node->getValue()->setSlug(
                $slug = $this->makeSlug($text)
            );

            $replacement = $this->view
                ->make($this->config('header_view'), $this->config())
                ->with(compact('text', 'size', 'slug'))
                ->render();
            $content     = str_replace($original, $replacement, $content);

            $prevSize = $size;
            $prevNode = $node;
        }

        if (\count($this->nodes) >= (int)$this->config('minimum_nodes')) {
            $toc = $this->view
                ->make($this->config('view'), $this->config())
                ->with('items', $rootNode->getChildren())
                ->render();
            $document->setContent($toc . $content);
        }
    }

    protected function createHeaderNode($size, $text)
    {
        return $this->nodes[] = Header::make($size, $text);
    }

    protected function isAllowedHeader($header)
    {
        return isset($this->config('headers')[ (int)$header ]) && true === $this->config('headers.' . (int)$header);
    }

    protected function makeSlug($text)
    {
        $slug = str_slug($text);
        if (\in_array($slug, $this->slugs, true)) {
            return $this->makeSlug($text . '_' . str_random(1));
        }

        return $this->slugs[] = $slug;
    }

}
