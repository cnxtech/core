<?php

namespace Codex\Documents\Processors\Parser\CommonMark;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Configuration;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\Xml;
use Webuni\CommonMark\TableExtension\Table;

class TableRenderer implements BlockRendererInterface, ConfigurationAwareInterface
{
    /** @var Configuration */
    protected $config;
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!$block instanceof Table) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        $attrs = [];
        foreach ($block->getData('attributes', []) as $key => $value) {
            $attrs[$key] = Xml::escape($value, true);
        }

        $separator = $htmlRenderer->getOption('inner_separator', "\n");



        $attrs = array_replace_recursive($attrs, $this->config->getConfig('element_attributes/table', []));
        return new HtmlElement('table', $attrs, $separator.$htmlRenderer->renderBlocks($block->children()).$separator);
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->config =$configuration;
    }
}
