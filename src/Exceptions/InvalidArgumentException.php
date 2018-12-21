<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

namespace Codex\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class InvalidArgumentException extends Exception implements Responsable
{
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
            response()->json($this->getMessage(), Response::HTTP_BAD_REQUEST) :
            response()->make($this->getMessage(), Response::HTTP_BAD_REQUEST);
    }
}
