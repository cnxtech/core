<?php


namespace Codex\Documents\Processors\Parser\CodexMark;


use League\CommonMark\Cursor;
use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Delimiter\DelimiterStack;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Reference\ReferenceMap;
use League\CommonMark\Util\RegexHelper;

class CloseBracketParser extends \League\CommonMark\Inline\Parser\CloseBracketParser
{
    public function parse(InlineParserContext $inlineContext)
    {
        $parseResult = parent::parse($inlineContext);

        if ($parseResult === false) {

            $cursor = $inlineContext->getCursor();

            $startPos      = $cursor->getPosition();
            $previousState = $cursor->saveState();

            // Look through stack of delimiters for a *
            $opener = $inlineContext->getDelimiterStack()->searchByCharacter(['*' ]);
            if ($opener === null) {
                return false;
            }

            if (!$opener->isActive()) {
                // no matched opener; remove from emphasis stack
                $inlineContext->getDelimiterStack()->removeDelimiter($opener);

                return false;
            }
            $isAbbreviation = $opener->getChar() === '*';

            if ( ! $isAbbreviation) {
                // no matched opener; remove from emphasis stack
                $inlineContext->getDelimiterStack()->removeDelimiter($opener);

                return false;
            }

//            $this->detach();
            $cursor->advance();
            // Check to see if we have a tryParseAbbreviation
            if ( ! ($abbreviation = $this->tryParseAbbreviation($cursor, $inlineContext->getReferenceMap(), $opener, $startPos))) {
                // No match
                $inlineContext->getDelimiterStack()->removeDelimiter($opener); // Remove this opener from stack
                $cursor->restoreState($previousState);

                return false;
            }

            $inline = $this->createInlineAbbreviation($abbreviation[ 'label' ], $abbreviation[ 'title' ]);
            $opener->getInlineNode()->replaceWith($inline);
            while (($label = $inline->next()->next()) !== null) {
                $inline->appendChild($label);
            }

            $delimiterStack = $inlineContext->getDelimiterStack();
            $stackBottom    = $opener->getPrevious();
            foreach ($this->environment->getInlineProcessors() as $inlineProcessor) {
                $inlineProcessor->processInlines($delimiterStack, $stackBottom);
            }
            if ($delimiterStack instanceof DelimiterStack) {
                $delimiterStack->removeAll($stackBottom);
            }

            return true;
        }

        return $parseResult;
    }

    protected function tryParseAbbreviation(Cursor $cursor, ReferenceMap $referenceMap, Delimiter $opener, $startPos)
    {
        if ($cursor->getCharacter() !== ':') {
            return false;
        }

        $previousState = $cursor->saveState();

        $cursor->advance();
        $cursor->advanceToNextNonSpaceOrNewline();
        $label = $opener->getInlineNode()->next()->next()->getContent();
        $cursor->advanceToNextNonSpaceOrNewline();

        $title = null;
        // make sure there's a space before the title:
        if (preg_match(RegexHelper::REGEX_WHITESPACE_CHAR, $cursor->peek(-1))) {
            $title = static::parseAbbreviationTitle($cursor) ?: '';
        }

        $cursor->advanceToNextNonSpaceOrNewline();
        $cursor->advanceToEnd();

        return [ 'label' => $label, 'title' => $title ];

    }

    public static function parseAbbreviationLabel(Cursor $cursor)
    {
        $oldState = $cursor->saveState();
        while (($c = $cursor->getCharacter()) !== null) {
            if ($c === '\\' && RegexHelper::isEscapable($cursor->peek())) {
                $cursor->advanceBy(2);
            } elseif (preg_match(RegexHelper::REGEX_WHITESPACE_CHAR, $c)) {
                break;
            } else {
                $cursor->advance();
            }
        }

        $newPos = $cursor->getPosition();
        $cursor->restoreState($oldState);

        $cursor->advanceBy($newPos - $cursor->getPosition());

        $res = $cursor->getPreviousText();

        return RegexHelper::unescape($res);
    }

    public static function parseAbbreviationTitle(Cursor $cursor)
    {

        $oldState = $cursor->saveState();
        while (($c = $cursor->getCharacter()) !== null) {
            if ($c === '\\' && RegexHelper::isEscapable($cursor->peek())) {
                $cursor->advanceBy(2);
            } elseif (preg_match('/[\t\n\x0b\x0c\x0d]+/', $c)) {
                break;
            } else {
                $cursor->advance();
            }
        }

        $newPos = $cursor->getPosition();
        $cursor->restoreState($oldState);

        $cursor->advanceBy($newPos - $cursor->getPosition());

        $res = $cursor->getPreviousText();

        return RegexHelper::unescape($res);
    }

    protected function createInlineAbbreviation($label, $title)
    {
        return new Abbreviation(null, $title);
    }

    protected function createInlineLink($url, $title)
    {
        return new Link($url, null, $title);
    }

    protected function createInlineImage($url, $title)
    {
        return new Image($url, null, $title);
    }

    protected function createInline($url, $title, $isImage)
    {
        if ($isImage) {
            return $this->createInlineImage($url, $title);
        }

        return $this->createInlineLink($url, $title);
    }


}
