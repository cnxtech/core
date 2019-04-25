<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author    Robin Radic
 * @license   https://codex-project.mit-license.org MIT License
 */

namespace Codex\Exceptions;

use Codex\Attributes\AttributeDefinition;
use GraphQL\Error\Error;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends Exception implements Responsable
{
    protected $status = Response::HTTP_NOT_FOUND;

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return $request->expectsJson() ?
            response()->json([ 'error' => $this->getMessage() ], $this->status) :
            response()->make($this->getMessage(), $this->status);
    }

    public function toApiError()
    {
        return new Error($this->getMessage());
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public static function addon($key)
    {
        return new static("Addon [{$key}] not found. You might need to enable it in the codex.php configuration.");
    }

    public static function project($key)
    {
        return new static("Project [{$key}] does not exist");
    }

    public static function document($key)
    {
        return new static("Document [{$key}] does not exist");
    }

    public static function revision($key)
    {
        return new static("Revision [{$key}] does not exist");
    }

    public static function definition($name, AttributeDefinition $parent = null)
    {
        $message = "Definition [{$name}] does not exist";
        if ($parent !== null) {
            $message = "Child {$message} in [$parent->name}]";
        }
        return new static($message);
    }
}
