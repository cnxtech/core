<?php

namespace Codex\Commands;

use Codex\Codex;
use Codex\Hooks;

class GetBackendData
{
    protected $requests = [];

    public function handle(Codex $codex)
    {
        $requests = [
            <<<'EOT'
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
        
        urls @assoc
        layout @assoc
        
        http {
            api_prefix
            documentation_prefix
            prefix
        }
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
            ,
        ];
        // put the requests array trough the hook to allow additional requests by others
        // a request can either be the query string or a assoc array with the query and variables
        $requests = Hooks::waterfall('commands.get_backend_data.request', $requests);
        // transform all requests that only have query string to assoc array
        $requests = array_map(function ($request) {
            return is_string($request) ? [ 'query' => $request, 'variables' => [] ] : $request;
        }, $requests);

        $fn = function () use ($codex, $requests) {
            $response = $codex->getApi()->executeBatchedQueries($requests);
            // transform all responses to the data arrays
            $data = array_map(function ($response) {
                return $response->data;
            }, $response);
            $data = array_replace_recursive(...$data);
            return $data;
        };

        // @todo: improve
        if (config('codex.cache.enabled', false) === true) {
            $cacheKey = md5(json_encode($requests));
            $data     = cache()->rememberForever($cacheKey, $fn);
        } else {
            $data = $fn();
        }


        // put the data trough the hook to allow external modification
        $data = Hooks::waterfall('commands.get_backend_data.response', $data);

        $options = config('app.debug') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES;
        $json    = json_encode($data, $options);
        $content = <<<EOT
window['BACKEND_DATA'] = {$json};
EOT;

        return $content;
    }
}
