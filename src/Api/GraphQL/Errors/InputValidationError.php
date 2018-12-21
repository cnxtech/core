<?php

namespace Codex\Api\GraphQL\Errors;

use GraphQL\Error\Error;
use Illuminate\Contracts\Support\MessageBag;

class InputValidationError extends Error
{
    protected $category = 'input-validation';

    /** @var \Illuminate\Contracts\Support\MessageBag */
    protected $errors;

    /**
     * InputValidationError constructor.
     *
     * @param \Illuminate\Contracts\Support\MessageBag $errors
     * @param                                          $path
     */
    public function __construct(MessageBag $errors, $path)
    {
        parent::__construct(
            'Input Validation Error',
            null,
            null,
            null,
            $path,
            null);
        $this->errors = $errors;
    }


    public function toSerializableArray()
    {
        $arr = parent::toSerializableArray();

        $arr['errors'] = $this->errors->toArray();

        return $arr;
    }


}
