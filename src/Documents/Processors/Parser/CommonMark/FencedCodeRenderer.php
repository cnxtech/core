<?php

namespace Codex\Documents\Processors\Parser\CommonMark;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Util\Xml;

class FencedCodeRenderer extends \League\CommonMark\Block\Renderer\FencedCodeRenderer
{
    /**
     * @param FencedCode               $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool                     $inTightList
     *
     * @return HtmlElement
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if ( ! ($block instanceof FencedCode)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        $attrs = [];
        foreach ($block->getData('attributes', []) as $key => $value) {
            $attrs[ $key ] = Xml::escape($value, true);
        }

        $infoWords           = $block->getInfoWords();
        $attrs[ 'language' ] = 'php';
        if (count($infoWords) !== 0 && strlen($infoWords[ 0 ]) !== 0) {
            $attrs[ 'language' ] = Xml::escape($infoWords[ 0 ], true);
        }

        $content  = Xml::escape($block->getStringContent()); //Asciimath
        $language = strtolower($attrs[ 'language' ]);
        if ($language === 'mermaid' || $language === 'chart' || $language === 'mathjax' || $language === 'katex' || $language === 'asciimath' || $language === 'nomnoml') {
            return new HtmlElement('c-code-renderer', $attrs, $content);
        }
//        if (starts_with($language, 'gist')) {
//            list($_language, $gist, $file) = explode(' ', $language);
//            $attrs[ 'language' ] = 'gist';
//            $attrs[ 'gist' ]     = $gist;
//            $attrs[ 'file' ]     = $file;
//            return new HtmlElement('c-code-renderer', $attrs, $content);
//        }

        return new HtmlElement('c-code-highlight', $attrs, $content);
    }
}
