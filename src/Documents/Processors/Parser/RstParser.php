<?php

namespace Codex\Documents\Processors\Parser;

use Codex\Exceptions\InvalidConfigurationException;
use Doctrine\RST\Parser as DoctrineParser;
use Gregwar\RST\Parser as GregwarParser;

class RstParser implements ParserInterface
{
    /** @var array */
    protected $options;

    protected function gregwarRst($string)
    {
        $parser  = new GregwarParser();
        $parsed  = $parser->parse($string);
        $content = $parsed->render();
        return $content;
    }

    protected function doctrineRst($string)
    {
        $parser  = new DoctrineParser();
        $parsed  = $parser->parse($string);
        $content = $parsed->render();
        return $content;
    }

    public function parse($string)
    {

        $variant = data_get($this->options, 'variant', 'gregwar');
        if ($variant === 'doctrine') {
            return $this->doctrineRst($string);
        }
        if ($variant === 'gregwar') {
            return $this->gregwarRst($string);
        }
        throw InvalidConfigurationException::reason('processor.parser.rst.variant');
    }

    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    protected function asdfas(
        ?InvalidConfigurationException $exception
    )
    {
        return [

        ];
    }
}
