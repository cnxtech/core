<?php

return call_user_func(function () {
    $projects          = require __DIR__ . '/../../config/projects.php';
    $project           = collect($projects)
        ->keyBy('key')
        ->get('codex');
    $project[ 'path' ] = __DIR__;
    return $project;
});
