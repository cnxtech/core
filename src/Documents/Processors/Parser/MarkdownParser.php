<?php

namespace Codex\Documents\Processors\Parser;

use cebe\markdown\GithubMarkdown;
use Codex\Concerns\Macroable;

class MarkdownParser extends GithubMarkdown implements ParserInterface
{
    use Macroable;
    protected $options = [];

    public function setOptions(array $options)
    {
        $this->options = $options;
        $this->html5   = data_get($options, 'html5', false);
    }

    /**
     * Renders a code block.
     */
    protected function renderCode($block)
    {
        $language = $block['language'] ?? 'php';
        $attr = ["language=\"{$language}\""];
        if (true === data_get($this->options, 'code.line_numbers', false)) {
            $attr[] = 'with-line-numbers';
        }
        if (true === data_get($this->options, 'code.command_line', false)) {
            $attr[] = 'with-command-line';
        }
        if (true === data_get($this->options, 'code.loader', false)) {
            $attr[] = 'with-loader';
        }

        /** @noinspection NonSecureHtmlspecialcharsUsageInspection */
        $code   = htmlspecialchars($block['content']."\n", ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $attr[] = "code=\"{$code}\"";
        $attr   = implode(' ', $attr);

        return "<c-code-highlight {$attr}>{$code}</c-code-highlight>\n";
    }

    protected function renderTable($block)
    {
        $table = parent::renderTable($block);
        $class = data_get($this->options, 'table.class', 'table');

        return str_replace('<table>', '<table class="'.$class.'">', $table);
    }
}
