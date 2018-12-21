<?php


namespace Codex\Concerns;


trait HasCodex
{
    public function getCodex()
    {
        return codex();
    }
}
