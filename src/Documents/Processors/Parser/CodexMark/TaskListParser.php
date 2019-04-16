<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Parser\AbstractBlockParser;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use League\CommonMark\Util\RegexHelper;


class TaskListParser extends AbstractBlockParser
{
    /**
     * @param ContextInterface $context
     * @param Cursor           $cursor
     *
     * @return bool
     */
    public function parse(ContextInterface $context, Cursor $cursor)
    {
        if ($cursor->isIndented() && ! ($context->getContainer() instanceof TaskListBlock)) {
            return false;
        }

        $tmpCursor = clone $cursor;
        $tmpCursor->advanceToNextNonSpaceOrTab();
        $rest = $tmpCursor->getRemainder();

        $data               = new TaskListData();
        $data->markerOffset = $cursor->getIndent();

        if (preg_match('/^[*+-]\s*\[[\sxX]\]/', $rest) === 1) {
            $data->type       = TaskListBlock::TYPE_UNORDERED;
            $data->delimiter  = null;
            $data->bulletChar = $rest[ 0 ];
            $markerLength     = 1;
        } elseif (($matches = RegexHelper::matchAll('/^(\d{1,9})([.)])/', $rest)) && ( ! ($context->getContainer() instanceof Paragraph) || $matches[ 1 ] === '1')) {
            $data->type       = TaskListBlock::TYPE_ORDERED;
            $data->start      = (int)$matches[ 1 ];
            $data->delimiter  = $matches[ 2 ];
            $data->bulletChar = null;
            $markerLength     = strlen($matches[ 0 ]);
        } else {
            return false;
        }

        // Make sure we have spaces after
        $nextChar = $tmpCursor->peek($markerLength);
        if ( ! ($nextChar === null || $nextChar === "\t" || $nextChar === ' ')) {
            return false;
        }

        // If it interrupts paragraph, make sure first line isn't blank
        $container = $context->getContainer();
        if ($container instanceof Paragraph && ! RegexHelper::matchAt(RegexHelper::REGEX_NON_SPACE, $rest, $markerLength)) {
            return false;
        }

        // get task status
        $cursor->advanceToNextNonSpaceOrTab(); // to start of marker
        $cursor->advanceBy($markerLength, true); // to end of marker
        $data->checked = $this->getTaskCheckValue($cursor);

        // We've got a match! Advance offset and calculate padding
        $cursor->advanceToNextNonSpaceOrTab(); // to start of marker
        $cursor->advanceBy($markerLength, true); // to end of marker
        $data->padding = $this->calculateListMarkerPadding($cursor, $markerLength);

        // add the list if needed
        if ( ! $container || ! ($container instanceof TaskListBlock) || ! $data->equals($container->getListData())) {
            $context->addBlock(new TaskListBlock($data));
        }

        // add the list item
        $context->addBlock(new TaskListItem($data));

        return true;
    }

    private function getTaskCheckValue(Cursor $cursor){
        $checked=false;

        $start          = $cursor->saveState();
        $pastCheckBox = false;

        $advanced = 0;
        while(!$pastCheckBox) {
            if($advanced > 5){
                $cursor->restoreState($start);
                return $checked;
            }
            if($cursor->peek(1) === '['){
                $value = $cursor->peek(2);
                $checked = $value === 'x' || $value === 'X';

                $advanced+=3;
                $cursor->advanceBy(3);
                $pastCheckBox = true;
            }   else {
                $advanced++;
                $cursor->advance();
            }
        }

        return $checked;
    }

    /**
     * @param Cursor $cursor
     * @param int    $markerLength
     *
     * @return int
     */
    private function calculateListMarkerPadding(Cursor $cursor, $markerLength)
    {
        $start          = $cursor->saveState();
        $spacesStartCol = $cursor->getColumn();

        while ($cursor->getColumn() - $spacesStartCol < 5) {
            if ( ! $cursor->advanceBySpaceOrTab()) {
                break;
            }
        }

        $blankItem         = $cursor->peek() === null;
        $spacesAfterMarker = $cursor->getColumn() - $spacesStartCol;

        if ($spacesAfterMarker >= 5 || $spacesAfterMarker < 1 || $blankItem) {
            $cursor->restoreState($start);
            $cursor->advanceBySpaceOrTab();

            return $markerLength + 1;
        }

        return $markerLength + $spacesAfterMarker;
    }
}
