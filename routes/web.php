<?php


Route::get("{projectKey?}/{revisionKey?}/{documentKey?}")
    ->prefix(codex()->getAttribute('http.documentation_prefix'))
    ->uses('DocumentController@getDocument')
    ->where('documentKey', '^(.*)')
    ->name('codex.documentation');

Route::get(codex()->attr('http.backend_data_url'))
    ->name('codex.backend_data')
    ->uses('DocumentController@getBackendData');

Route::redirect('/', codex()->getAttribute('http.documentation_prefix'))
    ->name('codex');
