<?php


namespace Codex\Documents\Processors\Parser\CodexMark;


use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;

class OpenAbbreviationBracketParser extends AbstractInlineParser
{
    /**
     * @return string[]
     */
    public function getCharacters()
    {
        return ['*'];
    }

    /**
     * @param InlineParserContext $inlineContext
     *
     * @return bool
     */
    public function parse(InlineParserContext $inlineContext)
    {
        $cursor = $inlineContext->getCursor();
        if ($cursor->peek() === '[') {
            $cursor->advanceBy(2);
            $node = new Text('*[', ['delim' => true]);
            $inlineContext->getContainer()->appendChild($node);

            // Add entry to stack for this opener
            $delimiter = new Delimiter('*', 1, $node, true, false, $cursor->getPosition());
            $inlineContext->getDelimiterStack()->push($delimiter);

            return true;
        }

        return false;
    }
}
