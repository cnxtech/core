<?php


namespace Codex\Contracts\Mergable;

/**
 * Interface ChildInterface
 *
 * @package Codex\Contracts\Mergable
 * @author  Robin Radic
 * @mixin \Codex\Mergable\Model
 */
interface ChildInterface
{

    /**
     * @return \Codex\Mergable\Model
     */
    public function getParent();

    public function setParent($parent);
}
