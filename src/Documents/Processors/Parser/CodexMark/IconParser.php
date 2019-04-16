<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\Inline\Parser\AbstractInlineParser;
use League\CommonMark\InlineParserContext;

class IconParser extends AbstractInlineParser
{

    protected $map = [];

    public function getCharacters()
    {
        return [ ':' ];
    }

    public function parse(InlineParserContext $inlineContext)
    {

        $cursor   = $inlineContext->getCursor();
        $previous = $cursor->peek(-1);
        if ($previous !== null && $previous !== ' ') {
            return false;
        }
        $saved = $cursor->saveState();
        $cursor->advance();

        $handle = $cursor->match('/^[a-z0-9\+\-_]+:/');
        if ( ! $handle) {
            $cursor->restoreState($saved);
            return false;
        }
        $next = $cursor->peek(0);
        if ($next !== null && $next !== ' ') {
            $cursor->restoreState($saved);
            return false;
        }
        $key = substr($handle, 0, -1);

        if(starts_with($key, ['fa-', 'icon-'])){
            $inline = new Icon($key);
            $inline->data[ 'attributes' ] = [ 'data-icon' => $key ];
            $inlineContext->getContainer()->appendChild($inline);
            return true;
        }

        $inline = new Emoji($key);
        $inline->data[ 'attributes' ] = [ 'class' => 'emoji', 'data-emoji' => $key ];
        $inlineContext->getContainer()->appendChild($inline);
        return true;
    }
}
