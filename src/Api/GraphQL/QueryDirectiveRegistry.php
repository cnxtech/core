<?php

namespace Codex\Api\GraphQL;

use Codex\Api\GraphQL\QueryDirectives\QueryDirective;

class QueryDirectiveRegistry
{
    protected $directives;

    public function __construct()
    {
        $this->directives = collect();
    }

    public function register(QueryDirective $directive)
    {
        $this->directives->put($directive->name(), $directive);
    }

    public function get($name)
    {
        return $this->directives->get($name);
    }

    public function has($name)
    {
        return $this->directives->has($name);
    }

    public function getNames()
    {
        return $this->directives->keys()->all();
    }
}
