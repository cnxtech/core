<?php

namespace Codex\Api\GraphQL\Queries;


use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class BaseQuery
{
    protected $argRules = [];

    /** @var \Illuminate\Contracts\Validation\Validator */
    protected $validator;

    protected function validateArgs(array $args = [])
    {
        $this->validator = app()->make(Validator::class, [ 'data' => $args, 'rules' => $this->argRules ]);

        try {
            return $this->validator->validate();
        }
        catch (ValidationException $e) {
            return false;
        }
    }
}
