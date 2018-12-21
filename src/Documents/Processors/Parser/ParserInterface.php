<?php

namespace Codex\Documents\Processors\Parser;

interface ParserInterface
{
    /**
     * parse method
     *
     * @param string $string
     *
     * @return string
     */
    public function parse($string);
    public function setOptions(array $options);
}
