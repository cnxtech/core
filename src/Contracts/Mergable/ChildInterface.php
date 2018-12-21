<?php


namespace Codex\Contracts\Mergable;

interface ChildInterface
{

    /**
     * @return null
     */
    public function getParent();

    public function setParent($parent);
}
