<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Util\Configuration;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\Xml;

class IconRenderer implements InlineRendererInterface, ConfigurationAwareInterface
{

    protected $config;

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof Icon)) {
            throw new \InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        $attrs = [];
        foreach ($inline->getData('attributes', []) as $key => $value) {
            $attrs[$key] = Xml::escape($value, true);
        }

        if (isset($inline->data['name'])) {
            $attrs['name'] = Xml::escape($inline->data['name'], true);
        }

        if($inline instanceof Emoji) {
            return new HtmlElement('c-emoji', $attrs, '', true);
        }
        return new HtmlElement('c-icon', $attrs, '', true);
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->config = $configuration;
    }
}
