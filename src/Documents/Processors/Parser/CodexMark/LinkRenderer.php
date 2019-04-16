<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;

class LinkRenderer extends \League\CommonMark\Inline\Renderer\LinkRenderer
{

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        $htmlElement = parent::render($inline, $htmlRenderer);
        $href        = $htmlElement->getAttribute('href');
        $href        = rawurldecode($href);
        $htmlElement->setAttribute('href', $href);
        return $htmlElement;
    }

}
