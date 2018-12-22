<?php


Route::get("{projectKey?}/{revisionKey?}/{documentPath?}")
    ->name('documentation')
    ->prefix(codex()->getAttribute('http.documentation_prefix'))
    ->uses('DocumentController@getDocument')
    ->where('documentPath', '^(.*)');
