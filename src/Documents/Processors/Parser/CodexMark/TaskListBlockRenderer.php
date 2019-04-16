<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\ListBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Xml;

class TaskListBlockRenderer implements BlockRendererInterface
{
    /**
     * @param ListBlock                $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool                     $inTightList
     *
     * @return HtmlElement
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if ( ! ($block instanceof TaskListBlock)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        $listData = $block->getListData();

        $tag = $listData->type === TaskListBlock::TYPE_UNORDERED ? 'ul' : 'ol';

        $attrs = [];
        foreach ($block->getData('attributes', []) as $key => $value) {
            $attrs[ $key ] = Xml::escape($value, true);
        }

        if ($listData->start !== null && $listData->start !== 1) {
            $attrs[ 'start' ] = (string)$listData->start;
        }

        $attrs['props'] = ['as' => $tag];

        return new HtmlElement(
            'c-task-list',
            $attrs,
            $htmlRenderer->getOption('inner_separator', "\n") . $htmlRenderer->renderBlocks(
                $block->children(),
                $block->isTight()
            ) . $htmlRenderer->getOption('inner_separator', "\n")
        );
    }
}
