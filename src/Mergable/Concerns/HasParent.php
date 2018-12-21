<?php

namespace Codex\Mergable\Concerns;

trait HasParent
{
    public function getParent()
    {
        return $this->parent;
    }

    public function _setParentAsProperty($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function _setParentAsRelation($parent)
    {
        $this->setRelation('parent', $parent);
        return $this;
    }
}
