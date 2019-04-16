<?php


namespace Codex\Documents\Processors\Parser\CodexMark;


use League\CommonMark\Inline\Element\AbstractInlineContainer;
use League\CommonMark\Inline\Element\Text;

class Abbreviation extends AbstractInlineContainer
{
    public function __construct($label, $title)
    {
        if (is_string($label)) {
            $this->appendChild(new Text($label));
        } elseif ($label instanceof Text) {
            $this->appendChild($label);
        }

        if ( ! empty($title)) {
            $this->data[ 'title' ] = $title;
        }
    }
}
