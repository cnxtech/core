<?php

namespace Codex\Documents\Processors\Parser;

use League\CommonMark\Block\Parser as BlockParser;
use League\CommonMark\Block\Renderer as BlockRenderer;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Inline\Parser as InlineParser;
use League\CommonMark\Inline\Processor as InlineProcessor;
use League\CommonMark\Inline\Renderer as InlineRenderer;
use Webuni\CommonMark\AttributesExtension;
use Webuni\CommonMark\TableExtension;


class CommonMarkParser implements ParserInterface, ExtensionInterface
{
    protected $options;

    /** @var \League\CommonMark\CommonMarkConverter */
    protected $converter;

    public function getConverter()
    {
        if ($this->converter === null) {
            $environment = new Environment();
            $environment->mergeConfig($this->options);
            $environment->addExtension($this);

            $config          = [
                'enable_em'     => true,
                'enable_strong' => true,
            ];
            $this->converter = new CommonMarkConverter($config, $environment);
        }
        return $this->converter;
    }

    public function parse($string)
    {
        $converter = $this->getConverter();
        $result    = $converter->convertToHtml($string);
        return $result;
    }

    public function setOptions(array $options = [])
    {
        $this->options = array_replace_recursive([
            'renderer'            => [
                'block_separator' => "\n",
                'inner_separator' => "\n",
                'soft_break'      => "\n",
            ],
            'safe'                => false, // deprecated option
            'html_input'          => Environment::HTML_INPUT_ALLOW,
            'allow_unsafe_links'  => true,
            'max_nesting_level'   => INF,
            'enable_abbreviation' => false,
        ], $options);

//        foreach ($this->options[ 'element_attributes' ] as $element => $attrs) {
//            $this->options[ 'element_attributes' ][ $element ] = collect($attrs)->mapWithKeys(function ($value, $key) {
//                return [ camel_case($key) => $value ];
//            })->toArray();
//        }
    }

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
            new CodexMark\TaskListParser(),
            new BlockParser\ListParser(),
            new BlockParser\IndentedCodeParser(),
            new BlockParser\LazyParagraphParser(),

            new TableExtension\TableParser(),
            new AttributesExtension\AttributesBlockParser(),
        ];
    }

    public function getInlineParsers()
    {
        return array_merge([
            new InlineParser\NewlineParser(),
            new InlineParser\BacktickParser(),
            new InlineParser\EscapableParser(),
            new InlineParser\EntityParser(),
            new InlineParser\EmphasisParser(),
//            new InlineParser\AutolinkParser(),
            new CodexMark\AutolinkParser(),
            new CodexMark\IconParser(),
            new InlineParser\HtmlInlineParser(),
        ], $this->options[ 'enable_abbreviation' ] ? [
            new CodexMark\CloseBracketParser(),
            new CodexMark\OpenAbbreviationBracketParser(),
        ] : [
            new InlineParser\CloseBracketParser(),
        ], [
            new InlineParser\BangParser(),
            new AttributesExtension\AttributesInlineParser(),
        ]);
    }

    public function getInlineProcessors()
    {
        return [
            new InlineProcessor\EmphasisProcessor(),

            new AttributesExtension\AttributesInlineProcessor(),
        ];
    }

    public function getDocumentProcessors()
    {
        return [
            new AttributesExtension\AttributesProcessor(),
        ];
    }


    public function getBlockRenderers()
    {
        return [
            'League\CommonMark\Block\Element\BlockQuote'   => new BlockRenderer\BlockQuoteRenderer(),
            'League\CommonMark\Block\Element\Document'     => new BlockRenderer\DocumentRenderer(),
//            'League\CommonMark\Block\Element\FencedCode'    => new BlockRenderer\FencedCodeRenderer(),
            'League\CommonMark\Block\Element\FencedCode'   => new CodexMark\FencedCodeRenderer(),
            'League\CommonMark\Block\Element\Heading'      => new BlockRenderer\HeadingRenderer(),
            'League\CommonMark\Block\Element\HtmlBlock'    => new BlockRenderer\HtmlBlockRenderer(),
            'League\CommonMark\Block\Element\IndentedCode' => new BlockRenderer\IndentedCodeRenderer(),

            CodexMark\TaskListBlock::class => new CodexMark\TaskListBlockRenderer(),
            CodexMark\TaskListItem::class  => new CodexMark\TaskListItemRenderer(),

            'League\CommonMark\Block\Element\ListBlock' => new BlockRenderer\ListBlockRenderer(),
            'League\CommonMark\Block\Element\ListItem'  => new BlockRenderer\ListItemRenderer(),

            'League\CommonMark\Block\Element\Paragraph'     => new BlockRenderer\ParagraphRenderer(),
            'League\CommonMark\Block\Element\ThematicBreak' => new BlockRenderer\ThematicBreakRenderer(),

            TableExtension\Table::class        => new CodexMark\TableRenderer(), //\Webuni\CommonMark\TableExtension\TableRenderer(),
            TableExtension\TableCaption::class => new TableExtension\TableCaptionRenderer(),
            TableExtension\TableRows::class    => new TableExtension\TableRowsRenderer(),
            TableExtension\TableRow::class     => new TableExtension\TableRowRenderer(),
            TableExtension\TableCell::class    => new TableExtension\TableCellRenderer(),
        ];
    }

    public function getInlineRenderers()
    {
        $renderers = [
            'League\CommonMark\Inline\Element\Code'       => new InlineRenderer\CodeRenderer(),
            'League\CommonMark\Inline\Element\Emphasis'   => new InlineRenderer\EmphasisRenderer(),
            'League\CommonMark\Inline\Element\HtmlInline' => new InlineRenderer\HtmlInlineRenderer(),
            'League\CommonMark\Inline\Element\Image'      => new InlineRenderer\ImageRenderer(),

            CodexMark\Icon::class                      => new CodexMark\IconRenderer(),
            CodexMark\Emoji::class                     => new CodexMark\IconRenderer(),

//            'League\CommonMark\Inline\Element\Link'       => new InlineRenderer\LinkRenderer(),
            'League\CommonMark\Inline\Element\Link'    => new CodexMark\LinkRenderer(),
            'League\CommonMark\Inline\Element\Newline' => new InlineRenderer\NewlineRenderer(),
            'League\CommonMark\Inline\Element\Strong'  => new InlineRenderer\StrongRenderer(),
            'League\CommonMark\Inline\Element\Text'    => new InlineRenderer\TextRenderer(),
        ];

        if ($this->options[ 'enable_abbreviation' ]) {
            $renderers[ CodexMark\Abbreviation::class ] = new CodexMark\AbbreviationRenderer();
        }

        return $renderers;
    }
}
