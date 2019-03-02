<?php

namespace Codex\Documents\Processors\Parser\CommonMark;

use League\CommonMark\Inline\Element\AbstractInlineContainer;

class Icon extends AbstractInlineContainer
{
    public function __construct($name)
    {
        $this->data[ 'name' ] = $name;
    }
}
