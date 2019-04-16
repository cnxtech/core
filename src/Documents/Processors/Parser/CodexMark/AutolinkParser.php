<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\Inline\Element\Link;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Util\UrlEncoder;

class AutolinkParser extends \League\CommonMark\Inline\Parser\AutolinkParser
{

    public function parse(InlineParserContext $inlineContext)
    {
        $cursor = $inlineContext->getCursor();
        if ($m = $cursor->match(self::EMAIL_REGEX)) {
            $email = substr($m, 1, -1);
            $inlineContext->getContainer()->appendChild(new Link('mailto:' . UrlEncoder::unescapeAndEncode($email), $email));

            return true;
        } elseif ($m = $cursor->match(self::OTHER_LINK_REGEX)) {
            $dest = substr($m, 1, -1);
            $inlineContext->getContainer()->appendChild(new Link($dest, $dest));

            return true;
        }

        return false;
    }

}
