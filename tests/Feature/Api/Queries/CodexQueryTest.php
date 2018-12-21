<?php

namespace Codex\Tests\Feature\Api\Queries;

use Codex\Tests\Feature\Api\ApiTestCase;

class CodexQueryTest extends ApiTestCase
{
    public function testConfigQuery()
    {
        $result = $this->executeQuery('{
            config {
                debug
                env
                fallback_locale
                locale
                name
                timezone
            }
        }');
        $config = array_only(config('app'), [ 'debug', 'env', 'fallback_locale', 'locale', 'name', 'timezone' ]);
        $this->assertEquals(compact('config'), $result->data);
    }

    public function testCodexQuery()
    {
        $result = $this->executeQuery('{
            codex {
                default_project
                description
                display_name
                addons                
            }
        }');
        $codex  = array_only(data_get($this->config, 'codex', []), [ 'default_project', 'description', 'display_name', 'addons' ]);
        $this->assertEquals(compact('codex'), $result->data);
    }
}
