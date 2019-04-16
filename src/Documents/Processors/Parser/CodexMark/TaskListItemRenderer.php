<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Xml;

class TaskListItemRenderer implements BlockRendererInterface
{
    /**
     * @param TaskListItem             $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool                     $inTightList
     *
     * @return string
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if ( ! ($block instanceof TaskListItem)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        $contents = $htmlRenderer->renderBlocks($block->children(), $inTightList);
        if (substr($contents, 0, 1) === '<') {
            $contents = "\n" . $contents;
        }
        if (substr($contents, -1, 1) === '>') {
            $contents .= "\n";
        }

        $attrs = [];
        foreach ($block->getData('attributes', []) as $key => $value) {
            $attrs[ $key ] = Xml::escape($value, true);
        }

        $attrs['props'] = ['checked' => $block->isChecked()];

        $li = new HtmlElement('c-task-list-item', $attrs, $contents);

        return $li;
    }
}
