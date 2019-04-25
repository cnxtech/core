<?php


namespace Codex\Contracts\Models;

/**
 * Interface ChildInterface
 *
 * @package Codex\Contracts\Mergable
 * @author  Robin Radic
 * @mixin \Codex\Models\Model
 */
interface ChildInterface
{

    /**
     * @return \Codex\Models\Model
     */
    public function getParent();

    public function setParent($parent);
}
