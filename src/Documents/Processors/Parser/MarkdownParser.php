<?php

namespace Codex\Documents\Processors\Parser;

use cebe\markdown\GithubMarkdown;

class MarkdownParser extends GithubMarkdown implements ParserInterface
{
    protected $options = [];

    public function setOptions(array $options)
    {
        $this->options = $options;
        $this->html5   = data_get($options, 'html5', false);
    }
}
