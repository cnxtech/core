<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\Block\Parser as BlockParser;
use League\CommonMark\Block\Renderer as BlockRenderer;
use League\CommonMark\Extension\Extension;
use League\CommonMark\Inline\Parser as InlineParser;
use League\CommonMark\Inline\Processor as InlineProcessor;
use League\CommonMark\Inline\Renderer as InlineRenderer;
use Webuni\CommonMark\AttributesExtension;
use Webuni\CommonMark\TableExtension;

class CodexCommonMarkExtension extends Extension
{
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
            new TaskListParser(),
            new BlockParser\ListParser(),
            new BlockParser\IndentedCodeParser(),
            new BlockParser\LazyParagraphParser(),

            new TableExtension\TableParser(),
            new AttributesExtension\AttributesBlockParser(),
        ];
    }

    public function getInlineParsers()
    {
        return [
            new InlineParser\NewlineParser(),
            new InlineParser\BacktickParser(),
            new InlineParser\EscapableParser(),
            new InlineParser\EntityParser(),
            new InlineParser\EmphasisParser(),
//            new InlineParser\AutolinkParser(),
            new AutolinkParser(),
            new IconParser(),
            new InlineParser\HtmlInlineParser(),
//            new InlineParser\CloseBracketParser(),
            new CloseBracketParser(),
            new InlineParser\OpenBracketParser(),
            new InlineParser\BangParser(),
            new OpenAbbreviationBracketParser(),

            new AttributesExtension\AttributesInlineParser()
        ];
    }

    public function getInlineProcessors()
    {
        return [
            new InlineProcessor\EmphasisProcessor(),

            new AttributesExtension\AttributesInlineProcessor()
        ];
    }

    public function getDocumentProcessors()
    {
        return [
            new AttributesExtension\AttributesProcessor()
        ];
    }


    public function getBlockRenderers()
    {
        return [
            'League\CommonMark\Block\Element\BlockQuote'   => new BlockRenderer\BlockQuoteRenderer(),
            'League\CommonMark\Block\Element\Document'     => new BlockRenderer\DocumentRenderer(),
//            'League\CommonMark\Block\Element\FencedCode'    => new BlockRenderer\FencedCodeRenderer(),
            'League\CommonMark\Block\Element\FencedCode'   => new FencedCodeRenderer(),
            'League\CommonMark\Block\Element\Heading'      => new BlockRenderer\HeadingRenderer(),
            'League\CommonMark\Block\Element\HtmlBlock'    => new BlockRenderer\HtmlBlockRenderer(),
            'League\CommonMark\Block\Element\IndentedCode' => new BlockRenderer\IndentedCodeRenderer(),

            TaskListBlock::class => new TaskListBlockRenderer(),
            TaskListItem::class  => new TaskListItemRenderer(),

            'League\CommonMark\Block\Element\ListBlock' => new BlockRenderer\ListBlockRenderer(),
            'League\CommonMark\Block\Element\ListItem'  => new BlockRenderer\ListItemRenderer(),

            'League\CommonMark\Block\Element\Paragraph'     => new BlockRenderer\ParagraphRenderer(),
            'League\CommonMark\Block\Element\ThematicBreak' => new BlockRenderer\ThematicBreakRenderer(),

            TableExtension\Table::class        => new TableRenderer(), //\Webuni\CommonMark\TableExtension\TableRenderer(),
            TableExtension\TableCaption::class => new TableExtension\TableCaptionRenderer(),
            TableExtension\TableRows::class    => new TableExtension\TableRowsRenderer(),
            TableExtension\TableRow::class     => new TableExtension\TableRowRenderer(),
            TableExtension\TableCell::class    => new TableExtension\TableCellRenderer(),
        ];
    }

    public function getInlineRenderers()
    {
        return [
            'League\CommonMark\Inline\Element\Code'       => new InlineRenderer\CodeRenderer(),
            'League\CommonMark\Inline\Element\Emphasis'   => new InlineRenderer\EmphasisRenderer(),
            'League\CommonMark\Inline\Element\HtmlInline' => new InlineRenderer\HtmlInlineRenderer(),
            'League\CommonMark\Inline\Element\Image'      => new InlineRenderer\ImageRenderer(),

            Icon::class                                => new IconRenderer(),
            Emoji::class                               => new IconRenderer(),
            Abbreviation::class                        => new AbbreviationRenderer(),

//            'League\CommonMark\Inline\Element\Link'       => new InlineRenderer\LinkRenderer(),
            'League\CommonMark\Inline\Element\Link'    => new LinkRenderer(),
            'League\CommonMark\Inline\Element\Newline' => new InlineRenderer\NewlineRenderer(),
            'League\CommonMark\Inline\Element\Strong'  => new InlineRenderer\StrongRenderer(),
            'League\CommonMark\Inline\Element\Text'    => new InlineRenderer\TextRenderer(),
        ];
    }

}
