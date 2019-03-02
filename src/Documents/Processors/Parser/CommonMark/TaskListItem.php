<?php

namespace Codex\Documents\Processors\Parser\CommonMark;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\ListData;
use League\CommonMark\Cursor;

class TaskListItem extends AbstractBlock
{
    /**
     * @var ListData
     */
    protected $listData;

    public function __construct(TaskListData $listData)
    {
        parent::__construct();

        $this->listData = $listData;
    }

    /**
     * Returns true if this block can contain the given block as a child node
     *
     * @param AbstractBlock $block
     *
     * @return bool
     */
    public function canContain(AbstractBlock $block)
    {
        return true;
    }

    /**
     * Returns true if block type can accept lines of text
     *
     * @return bool
     */
    public function acceptsLines()
    {
        return false;
    }

    /**
     * Whether this is a code block
     *
     * @return bool
     */
    public function isCode()
    {
        return false;
    }

    public function isChecked()
    {
        return $this->listData->checked === true;
    }

    public function matchesNextLine(Cursor $cursor)
    {
        if ($cursor->isBlank()) {
            if ($this->firstChild === null) {
                return false;
            }

            $cursor->advanceToNextNonSpaceOrTab();
        } elseif ($cursor->getIndent() >= $this->listData->markerOffset + $this->listData->padding) {
            $cursor->advanceBy($this->listData->markerOffset + $this->listData->padding, true);
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param Cursor $cursor
     * @param int    $currentLineNumber
     *
     * @return bool
     */
    public function shouldLastLineBeBlank(Cursor $cursor, $currentLineNumber)
    {
        return $cursor->isBlank() && $this->startLine < $currentLineNumber;
    }
}
