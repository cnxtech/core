<?php


namespace Codex\Mergable\Concerns;

trait HasChildren
{
    public function getChildren()
    {
        return $this->children;
    }

    public function _setChildrenProperty($children)
    {
        $this->children = $children;
    }

    public function _setChildrenRelation($children)
    {
        $this->setRelation('children', $children);
    }
}
