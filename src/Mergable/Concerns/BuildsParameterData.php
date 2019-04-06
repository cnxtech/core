<?php


namespace Codex\Mergable\Concerns;


use Codex\Contracts\Mergable\ChildInterface;
use Codex\Contracts\Mergable\Model;
use Codex\Support\DotArrayWrapper;

trait BuildsParameterData
{
    protected function buildParameterData(Model $model)
    {
        $data   = DotArrayWrapper::make()->setItems($model);
        $target = $model;
        while ($target instanceof ChildInterface) {
            $parent        = $target->getParent();
            $name          = $parent->getClassSlug();
//            $data[ $name ] = $parent->getAttributes();
            $data->set($name, $parent);
//            $data[ $name ] = $parent;
            $target        = $parent;
        }
        return $data;
    }
}
