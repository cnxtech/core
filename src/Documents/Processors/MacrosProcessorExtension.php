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
use Codex\Attributes\AttributeDefinitionType;
use Codex\Contracts\Documents\Document;
use Codex\Documents\Processors\Macros\Macro;

/**
 * This is the class DocTagsFilter.
 *
 * @author         CLI
 * @copyright      Copyright (c) 2015, CLI. All rights reserved
 *
 * @Processor("macros", config="config", after={"parser"})
 */
class MacrosProcessorExtension extends ProcessorExtension implements ProcessorInterface
{
    const REGEXP = '/\*codex:(.*?)\*/';

    protected $defaultConfig = 'codex.processor-defaults.macros';

    protected $after = [];

    /** @var \Codex\Codex */
    public $codex;

    /** @var \Codex\Projects\Project */
    public $project;

    /** @var \Codex\Documents\Document */
    public $document;

    public function getName()
    {
        return 'macros';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $definition->setType(new AttributeDefinitionType('array.scalarPrototype'));
    }

    public function process(Document $document)
    {

        $definitions = $this->getAllMacroDefinitions();
        $dom         = $document->getDOM();
        $comments    = $dom->xpath('.//comment()');
        /** @var Macro[] $macros */
        $macros = [];
        for ($commentIndex = 0; $commentIndex < $comments->length; $commentIndex++) {
            $comment = $comments->item($commentIndex);
            preg_match_all(MacrosProcessorExtension::REGEXP, $comment->nodeValue, $matches);

            foreach ($matches[ 0 ] as $i => $raw) {
                $definition = Macro::extractDefinition($matches[ 1 ][ $i ]);
                if (false === array_key_exists($definition, $definitions)) {
                    continue;
                }
                $macros[] = $macro = $this->createMacro($raw, $matches[ 1 ][ $i ]);
                $macro->setHandler($definitions[ $macro->definition ]);

                $parent = $comment->parentNode;
//                $parent->removeChild($comment);
                while ($parent) {


                    if (isset($parent->tagName) && $parent->tagName === 'p') {
                        $this->changeName($parent, 'span');
                    }
                    $parent = $parent->parentNode;
                }
            }
        }
        $dom->saveToDocument();

        foreach ($macros as $macro) {
            $content = $document->getContent();
            $result  = $macro->run();
            // @todo improve
            // negative lookbehind (?<!\") is a quick fix to counter replacing the macro for:
            // <c-code-highlight code="<!--*codex:phpdoc:method('Codex\Codex::get()', true, true, 'namespace,tags')*-->"></c-code-highlight>
            $pattern = '/(?<!\")\<\!\-\-' . preg_quote($macro->raw, '/') . '\-\-\>/';
            $content = preg_replace($pattern, $result."\n", $content, 1);
            $document->setContent($content);
        }
    }

    public function process2(Document $document)
    {
        $definitions          = $this->getAllMacroDefinitions();
        $dom                  = $document->getDOM();
        $comments             = $dom->xpath('.//comment()');
        $previousComment      = null;
        $previousCommentMacro = null;

        for ($commentIndex = 0; $commentIndex < $comments->length; $commentIndex++) {
            $comment          = $comments->item($commentIndex);
            $nextComment      = null;
            $nextCommentMacro = null;
            $nextCommentIndex = $commentIndex;
            while ($comments->length + 1 >= $nextCommentIndex) { // if ($comments->length + 1 > $commentIndex) {
                $nextCommentIndex++;
                $nextComment           = $comments->item($nextCommentIndex);
                $hasNextCommentMatches = preg_match_all(MacrosProcessorExtension::REGEXP, $nextComment, $nextCommentMatches) > 0;
                if ($hasNextCommentMatches) {
                    $nextCommentRaw = $nextCommentMatches[ 0 ][ 0 ];
                    $definition     = Macro::extractDefinition($nextCommentMatches[ 1 ][ 0 ]);
                    if (false === array_key_exists($definition, $definitions)) {
                        continue;
                    }
                    $nextCommentMacro = $this->createMacro($nextCommentRaw, $nextCommentMatches[ 1 ][ 0 ]);
                    $nextCommentMacro->setHandler($definitions[ $nextCommentMacro->definition ]);
                    break;
                }
            }

            preg_match_all(MacrosProcessorExtension::REGEXP, $comment->nodeValue, $matches);

            foreach ($matches[ 0 ] as $i => $raw) {
                $definition = Macro::extractDefinition($matches[ 1 ][ $i ]);
                if (false === array_key_exists($definition, $definitions)) {
                    continue;
                }
                $macro = $this->createMacro($raw, $matches[ 1 ][ $i ]);
                $macro->setHandler($definitions[ $macro->definition ]);

                if ($macro->isClosing()) {
                    continue; // todo
                }
                if ($nextCommentMacro && $nextCommentMacro->definition === $macro->definition && $nextCommentMacro->isClosing()) {

                    continue; // todo

                    $contentNodes = [];
                    $nextSibling  = $comment->nextSibling;
                    while ($nextSibling) {
                        $contentNodes[] = $nextSibling;
                        $nextSibling    = $nextSibling->nextSibling;
                    }
                }
                $result = $macro->run();
                $el     = \FluentDOM::Query($result, 'html-fragment')->get(0);
                $el2    = $comment->ownerDocument->createElement($el->nodeName, $el->nodeValue);


                $new = \FluentDOM::Query($result, 'text/html');
                $comment->before($new->xpath('//body/*')[ 0 ]);
                $comment->insertBefore($el2, $comment);


                $parent = $comment->parentNode;
                $parent->removeChild($comment);
                while ($parent) {


                    if (isset($parent->tagName) && $parent->tagName === 'p') {
                        $this->changeName($parent, 'span');
                    }
                    $parent = $parent->parentNode;
                }
            }
            $previousComment = $comment;
        }
        $dom->saveToDocument();
    }

    protected function changeName(\FluentDOM\DOM\Element $node, $name)
    {
        $newnode = $node->ownerDocument->createElement($name);
        foreach ($node->childNodes as $child) {
            $child = $node->ownerDocument->importNode($child, true);
            $newnode->appendChild($child);
        }
        foreach ($node->attributes as $attrName => $attrNode) {
            $newnode->setAttribute($attrName, $attrNode);
        }
        $node->parentNode->replaceChild($newnode, $node);
        return $newnode;
    }

    protected function createMacro($raw, $cleaned)
    {
        $macro           = new Macro($raw, $cleaned);
        $macro->codex    = $this->codex;
        $macro->project  = $this->project;
        $macro->document = $this->document;

        return $macro;
    }

    /**
     * This will get the configured macros. It merges (if defined) the global config, project config and document attributes.
     *
     * Project macros will overide global macros with the same name.
     * Document macros will overide project macros with the same name.
     * Other then that, everything will be merged/inherited.
     *
     * @return array The collected macros as id(used for regex) > handler(the class string name with @ method callsign)
     */
    protected function getAllMacroDefinitions()
    {
        return $this->config();
    }

    public function setDocument($document)
    {
        parent::setDocument($document);
        if ($document === null) {
            $this->revision = $this->project = $this->codex = null;
            return $this;
        }
        $this->revision = $document->getRevision();
        $this->project  = $document->getProject();
        $this->codex    = $document->getCodex();
        return $this;
    }
}
