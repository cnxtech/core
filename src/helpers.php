<?php


if ( ! function_exists('codex')) {
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
}
