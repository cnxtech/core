<?php


namespace Codex\Mergable\Concerns;


use Codex\Contracts\Mergable\ChildInterface;
use Codex\Contracts\Mergable\Model;

trait BuildsParameterData
{
    protected function buildParameterData(Model $model)
    {
        $data   = [];
        $target = $model;
        while ($target instanceof ChildInterface) {
            $parent        = $target->getParent();
            $name          = $parent->getClassSlug();
            $data[ $name ] = $parent->getAttributes();
            $target        = $parent;
        }
        return $data;
    }
}
