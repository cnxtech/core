<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Codex\Processors\Toc\Header;
use Illuminate\Contracts\View\Factory;

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

    protected $defaultConfig = 'codex.processors.toc';

    protected $depends = ['parser'];

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

    public function handle(Document $document)
    {
        $content = $document->getContent();
        $total = preg_match_all($this->config['regex'], $content, $matches);
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
            $original = $matches[0][$h];
            $size = (int) $matches[1][$h];
            $text = $matches[2][$h];
            if (\in_array($size, $this->config['disable'], true)) {
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

            $node->getValue()->setSlug(
                $slug = $this->makeSlug($text)
            );

//            $link = '';
//            if (true === $this->config['header_link_show']) {
//                $link = "<a href='#{$slug}' class='{$this->config['header_link_class']}'>#</a>";
//            }
//            $replacement = "<h{$size} id='{$slug}'><span>{$text}</span>{$link}</h{$size}>";
            $replacement = "<toc-header title='{$text}' size='{$size}' slug='{$slug}' link-class='{$this->config['header_link_class']}'></toc-header>";
            $content = str_replace($original, $replacement, $content);

            $prevSize = $size;
            $prevNode = $node;
        }

        $toc = $this->view
            ->make($this->config['view'], $this->config)
            ->with('items', $rootNode->getChildren())
            ->render();

        if (\count($this->nodes) >= (int) $this->config['minimum_nodes']) {
            $document->setContent("<toc-list class=\"{$this->config['list_class']}\">{$toc}</toc-list>".$content);
        }
    }

    protected function createHeaderNode($size, $text)
    {
        return $this->nodes[] = Header::make($size, $text);
    }

    protected function isAllowedHeader($header)
    {
        return isset($this->config['headers'][(int) $header]) && true === $this->config['headers'][(int) $header];
    }

    protected function makeSlug($text)
    {
        $slug = str_slug($text);
        if (\in_array($slug, $this->slugs, true)) {
            return $this->makeSlug($text.'_'.str_random(1));
        }

        return $this->slugs[] = $slug;
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        // TODO: Implement defineConfigAttributes() method.
    }

    public function process(Document $document)
    {
        // TODO: Implement process() method.
    }
}
