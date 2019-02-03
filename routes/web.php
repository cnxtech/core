<?php


Route::get("{projectKey?}/{revisionKey?}/{documentKey?}")
    ->name('codex.documentation')
    ->prefix(codex()->getAttribute('http.documentation_prefix'))
    ->uses('DocumentController@getDocument')
    ->where('documentKey', '^(.*)');

Route::get('/', function () {
    return redirect()->route('codex.documentation');
})->name('codex');

Route::get(codex()->attr('http.backend_data_url'))
    ->name('codex.backend_data')
    ->uses('DocumentController@getBackendData');
