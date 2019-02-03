<?php


Route::get("{projectKey?}/{revisionKey?}/{documentKey?}")
    ->name('codex.documentation')
    ->prefix(codex()->getAttribute('http.documentation_prefix'))
    ->uses('DocumentController@getDocument')
    ->where('documentKey', '^(.*)');

Route::get('/', function () {
    return redirect()->route('codex.documentation');
})->name('codex');

Route::get('/backend_data.js', function () {

    $r = codex()->getApi()->executeQuery(<<<'EOT'
query {
    codex {
        cache {
            enabled
            key
            minutes
        }
        
        display_name
        description
        default_project
        urls {
            api
            documentation
            root
        }
        http {
            api_prefix
            documentation_prefix
            prefix
        }
        layout @assoc
        projects {
            key
            display_name
            description
            default_revision
            revisions {
                key
                default_document
            }
        }
    }
    config {
        debug
        fallback_locale
        locale
        name
        timezone
        url
    }
}
EOT
    );

    $json = json_encode($r->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return response(<<<EOT
window['BACKEND_DATA'] = {$json};
EOT
        , 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
        ]);
});
