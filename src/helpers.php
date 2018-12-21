<?php

/**
 * codex method
 *
 * @return \Codex\Codex
 */
function codex()
{
    $codex = app()->make('codex');
    return $codex;
}
