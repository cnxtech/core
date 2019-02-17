<?php

namespace Codex\Documents\Processors\Parser;

use Codex\Documents\Processors\Parser\CommonMark\CodexCommonMarkExtension;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Webuni\CommonMark\AttributesExtension\AttributesExtension;
use Webuni\CommonMark\TableExtension\TableExtension;

class CommonMarkParser implements ParserInterface
{
    protected $options;

    /** @var \League\CommonMark\CommonMarkConverter */
    protected $converter;

    public function getConverter()
    {
        if ($this->converter === null) {
            $environment = new Environment();
            $environment->addExtension(new CodexCommonMarkExtension());
            $environment->mergeConfig([
                'renderer'           => [
                    'block_separator' => "\n",
                    'inner_separator' => "\n",
                    'soft_break'      => "\n",
                ],
                'safe'               => false, // deprecated option
                'html_input'         => Environment::HTML_INPUT_ALLOW,
                'allow_unsafe_links' => true,
                'max_nesting_level'  => INF,
            ]);
            $environment->addExtension(new TableExtension());
            $environment->addExtension(new AttributesExtension());

            $config          = [
//                'renderer' => [
//                    'block_separator' => "\n",
//                    'inner_separator' => "\n",
//                    'soft_break'      => "\n",
//                ],
                'enable_em'     => true,
                'enable_strong' => true,
//                'use_asterisk' => true,
//                'use_underscore' => true,
            ];
            $this->converter = new CommonMarkConverter($config, $environment);
        }
        return $this->converter;
    }

    public function parse($string)
    {
        $converter = $this->getConverter();
        $result    = $converter->convertToHtml($string);
        return $result;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
