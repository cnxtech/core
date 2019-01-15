<?php

namespace Codex\Documents\Processors;

use Codex\Attributes\AttributeDefinition;
use Codex\Contracts\Documents\Document;
use Codex\Mergable\Commands\MergeAttributes;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Yaml\Yaml;

class AttributesProcessorExtension extends ProcessorExtension implements PreProcessorInterface
{
    use DispatchesJobs;

    protected $defaultConfig = 'codex.processor-defaults.attributes';

    public function getName()
    {
        return 'attributes';
    }

    public function defineConfigAttributes(AttributeDefinition $definition)
    {
        $tags = $definition->add('tags', 'array.arrayPrototype');
        $tags->add('open', 'string');
        $tags->add('close', 'string');
    }

    public function preProcess(Document $document)
    {
        // As this is a PRE processor, this operation is executed for each new instance of Document.
        // So instead of reading the whole file which is uneccesary, we only read until the close tag position
        // Using that position, we will override the document's content resolver and make that start reading from there untill EOF
        $stream      = $document->getFiles()->readStream($document->getPath());
        $headContent = head(preg_split('/\n/', trim(fread($stream, 20))));
        if ( ! $this->checkHasOpenTag($headContent)) {
            return;
        }
        fseek($stream, strlen($headContent), SEEK_SET);
        $close = $this->mapPregQuote($this->config('tags.*.close'));
        $done  = false;
        while ( ! feof($stream) && ($buffer = fgets($stream, 4096)) !== false && $done === false) {
            $headContent .= $buffer;
            $count       = preg_match('/^(?:' . implode('|', $close) . ')/', $buffer);
            $done        = $count > 0;
        }
        $pointerPosition = ftell($stream);
        fclose($stream);

        $attributes = $this->getAttributes($headContent);
        $this->dispatch(new MergeAttributes($document, $attributes));

//        foreach (array_dot($attributes) as $key => $value) {
//            $document->setAttribute($key, $value);
//        }

        $document->setContentResolver(function (Document $document) use ($pointerPosition) {
            $stream = $document->getFiles()->readStream($document->getPath());
            fseek($stream, $pointerPosition, SEEK_SET);
            $bodyContent = '';
            while ( ! feof($stream) && ($buffer = fread($stream, 4096)) !== false) {
                $bodyContent .= $buffer;
            }
            fclose($stream);
            return $bodyContent;
        });
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
