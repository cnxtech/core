<?php
$projects          = require __DIR__ . '/../../config/projects.php';
$project           = collect($projects)
    ->keyBy('key')
    ->get('blade-extensions');
$project[ 'path' ] = __DIR__;
return $project;
