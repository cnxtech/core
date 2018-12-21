<?php

namespace Codex\Tests\Feature\Api;

use Codex\Codex;
use Codex\Projects\ProjectCollection;
use Codex\Tests\Feature\FeatureTestCase;
use Codex\Tests\Fixture\CreatesConfigData;
use Codex\Tests\TestCase;
use GraphQL\Executor\ExecutionResult;

class ApiTestCase extends FeatureTestCase
{
    protected $globalVariables = [];

    protected function executeQuery($query, $variables = [], $assertResult = true)
    {
        $variables = array_replace_recursive($this->globalVariables, $variables);
        $result    = graphql()->queryAndReturnResult($query, null, $variables);
        if ($assertResult) {
            $this->assertNoResultErrors($result);
        }
        return $result;
    }

    protected function assertNoResultErrors(ExecutionResult $result)
    {
        $hasErrors = ! is_array($result->errors) || count($result->errors) > 1;
        $this->assertFalse($hasErrors);
    }

    protected function setUp()
    {
        parent::setUp();

    }


}
