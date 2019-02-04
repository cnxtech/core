<?php

namespace Codex\Commands;

use Codex\Codex;
use Codex\Hooks;

class GetBackendData
{
    public function handle(Codex $codex)
    {
        $r = $codex->getApi()->executeQuery(<<<'EOT'
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
        );


        $data = Hooks::waterfall('GetBackendData', $r->data);
        return $data;
    }
}
