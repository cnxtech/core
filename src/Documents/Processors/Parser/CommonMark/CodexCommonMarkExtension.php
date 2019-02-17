<?php

namespace Codex\Documents\Processors\Parser\CommonMark;

use League\CommonMark\Block\Parser as BlockParser;
use League\CommonMark\Block\Renderer as BlockRenderer;
use League\CommonMark\Extension\Extension;
use League\CommonMark\Inline\Parser as InlineParser;
use League\CommonMark\Inline\Processor as InlineProcessor;
use League\CommonMark\Inline\Renderer as InlineRenderer;


class CodexCommonMarkExtension extends Extension
{
    /**
     * @return BlockParser\BlockParserInterface[]
     */
    public function getBlockParsers()
    {
        return [
            // This order is important
            new BlockParser\BlockQuoteParser(),
            new BlockParser\ATXHeadingParser(),
            new BlockParser\FencedCodeParser(),
            new BlockParser\HtmlBlockParser(),
            new BlockParser\SetExtHeadingParser(),
            new BlockParser\ThematicBreakParser(),
            new BlockParser\ListParser(),
            new BlockParser\IndentedCodeParser(),
            new BlockParser\LazyParagraphParser(),
        ];
    }

    /**
     * @return InlineParser\InlineParserInterface[]
     */
    public function getInlineParsers()
    {
        return [
            new InlineParser\NewlineParser(),
            new InlineParser\BacktickParser(),
            new InlineParser\EscapableParser(),
            new InlineParser\EntityParser(),
            new InlineParser\EmphasisParser(),
            new InlineParser\AutolinkParser(),
//            new AutolinkParser(),
            new InlineParser\HtmlInlineParser(),
            new InlineParser\CloseBracketParser(),
            new InlineParser\OpenBracketParser(),
            new InlineParser\BangParser(),
        ];
    }

    /**
     * @return InlineProcessor\InlineProcessorInterface[]
     */
    public function getInlineProcessors()
    {
        return [
            new InlineProcessor\EmphasisProcessor(),
        ];
    }

    /**
     * @return BlockRenderer\BlockRendererInterface[]
     */
    public function getBlockRenderers()
    {
        return [
            'League\CommonMark\Block\Element\BlockQuote'    => new BlockRenderer\BlockQuoteRenderer(),
            'League\CommonMark\Block\Element\Document'      => new BlockRenderer\DocumentRenderer(),
//            'League\CommonMark\Block\Element\FencedCode'    => new BlockRenderer\FencedCodeRenderer(),
            'League\CommonMark\Block\Element\FencedCode'    => new FencedCodeRenderer(),
            'League\CommonMark\Block\Element\Heading'       => new BlockRenderer\HeadingRenderer(),
            'League\CommonMark\Block\Element\HtmlBlock'     => new BlockRenderer\HtmlBlockRenderer(),
            'League\CommonMark\Block\Element\IndentedCode'  => new BlockRenderer\IndentedCodeRenderer(),
            'League\CommonMark\Block\Element\ListBlock'     => new BlockRenderer\ListBlockRenderer(),
            'League\CommonMark\Block\Element\ListItem'      => new BlockRenderer\ListItemRenderer(),
            'League\CommonMark\Block\Element\Paragraph'     => new BlockRenderer\ParagraphRenderer(),
            'League\CommonMark\Block\Element\ThematicBreak' => new BlockRenderer\ThematicBreakRenderer(),
        ];
    }

    /**
     * @return InlineRenderer\InlineRendererInterface[]
     */
    public function getInlineRenderers()
    {
        return [
            'League\CommonMark\Inline\Element\Code'       => new InlineRenderer\CodeRenderer(),
            'League\CommonMark\Inline\Element\Emphasis'   => new InlineRenderer\EmphasisRenderer(),
            'League\CommonMark\Inline\Element\HtmlInline' => new InlineRenderer\HtmlInlineRenderer(),
            'League\CommonMark\Inline\Element\Image'      => new InlineRenderer\ImageRenderer(),
            'League\CommonMark\Inline\Element\Link'       => new InlineRenderer\LinkRenderer(),
            'League\CommonMark\Inline\Element\Newline'    => new InlineRenderer\NewlineRenderer(),
            'League\CommonMark\Inline\Element\Strong'     => new InlineRenderer\StrongRenderer(),
            'League\CommonMark\Inline\Element\Text'       => new InlineRenderer\TextRenderer(),
        ];
    }

}
