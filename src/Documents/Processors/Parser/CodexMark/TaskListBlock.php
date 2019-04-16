<?php

namespace Codex\Documents\Processors\Parser\CodexMark;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\ListData;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;

class TaskListBlock extends AbstractBlock
{
    const TYPE_UNORDERED = 'Bullet';
    const TYPE_ORDERED = 'Ordered';

    /**
     * @var bool
     */
    protected $tight = false;

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
     * @return ListData
     */
    public function getListData()
    {
        return $this->listData;
    }

    /**
     * @return bool
     */
    public function endsWithBlankLine()
    {
        if ($this->lastLineBlank) {
            return true;
        }

        if ($this->hasChildren()) {
            return $this->lastChild()->endsWithBlankLine();
        }

        return false;
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
        return $block instanceof TaskListItem;
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

    public function matchesNextLine(Cursor $cursor)
    {
        return true;
    }

    public function finalize(ContextInterface $context, $endLineNumber)
    {
        parent::finalize($context, $endLineNumber);

        $this->tight = true; // tight by default

        foreach ($this->children() as $item) {
            // check for non-final list item ending with blank line:
            if ($item->endsWithBlankLine() && $item !== $this->lastChild()) {
                $this->tight = false;
                break;
            }

            // Recurse into children of list item, to see if there are
            // spaces between any of them:
            foreach ($item->children() as $subItem) {
                if ($subItem->endsWithBlankLine() && ($item !== $this->lastChild() || $subItem !== $item->lastChild())) {
                    $this->tight = false;
                    break;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isTight()
    {
        return $this->tight;
    }

    /**
     * @param bool $tight
     *
     * @return $this
     */
    public function setTight($tight)
    {
        $this->tight = $tight;

        return $this;
    }
}
