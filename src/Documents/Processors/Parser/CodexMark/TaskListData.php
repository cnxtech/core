<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

class TaskListData
{

    /**
     * @var int|null
     */
    public $start;

    /**
     * @var int
     */
    public $padding = 0;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $delimiter;

    /**
     * @var string
     */
    public $bulletChar;

    /**
     * @var int
     */
    public $markerOffset;

    public $checked = false;

    /**
     * @param TaskListData $data
     *
     * @return bool
     */
    public function equals(TaskListData $data)
    {
        return $this->type === $data->type &&
            $this->delimiter === $data->delimiter &&
            $this->bulletChar === $data->bulletChar;
    }
}
