<?php


Route::get("{projectKey?}/{revisionKey?}/{documentPath?}")
    ->name('codex.documentation')
    ->prefix(codex()->getAttribute('http.documentation_prefix'))
    ->uses('DocumentController@getDocument')
    ->where('documentPath', '^(.*)');
