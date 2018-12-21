<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Symfony\Component\Yaml\Yaml;

class AttributeProcessorExtension extends ProcessorExtension
{
    protected $defaultConfig = 'codex.processor-defaults.attributes';

    protected $pre = true;

    public function getName()
    {
        return 'attributes';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $tags = $definition->add('tags', 'array')->setDefault([]);
    }

    public function process(Document $document)
    {
        $stream      = $document->getFiles()->readStream($document->getPath());
        $headContent = trim(fread($stream, 10));
        if ( ! $this->checkHasOpenTag($headContent)) {
            return;
        }
        $close = $this->mapPregQuote($this->config('tags.*.close'));
        $done  = false;
        while (($buffer = fgets($stream, 4096)) !== false && $done === false) {
            $headContent .= $buffer;
            $count       = preg_match('/^(?:' . implode('|', $close) . ')/', $buffer);
            $done        = $count > 0;
        }
        $attributes = $this->getAttributes($headContent);
//        $document->setAttribute('attributes', $attributes);
        foreach (array_dot($attributes) as $key => $value) {
            $document->setAttribute($key, $value);
        }
        $bodyContent = '';
        while ( ! feof($stream) && ($buffer = fread($stream, 4096)) !== false) {
            $bodyContent .= $buffer;
        }
        fclose($stream);
        $document->setContent($bodyContent);
    }

    public function checkHasOpenTag(string $str)
    {
        $open  = $this->mapPregQuote($this->config('tags.*.open'));
        $count = preg_match('/^(?:' . implode('|', $open) . ')/', $str);
        return $count > 0;
    }

    protected function getTagsPattern()
    {
        $open  = $this->mapPregQuote($this->config('tags.*.open'));
        $close = $this->mapPregQuote($this->config('tags.*.close'));
        return '/^(?:' . implode('|', $open) . ')([\w\W]*?)(?:' . implode('|', $close) . ')/';
    }

    protected function mapPregQuote(array $items)
    {
        return array_map(function ($item) {
            return preg_quote($item, '/');
        }, $items);
    }

    protected function getAttributes($content)
    {
        $attributes = [];
        $pattern    = $this->getTagsPattern();
        if (1 === preg_match($pattern, $content, $matches)) {
            $data = Yaml::parse($matches[ 1 ]);
            if (\is_array($data)) {
                $attributes = $data;
            }
        }
        return $attributes;
    }
}
