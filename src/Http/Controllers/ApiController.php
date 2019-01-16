<?php

namespace Codex\Http\Controllers;

use Illuminate\Http\Response;
use Nuwave\Lighthouse\Schema\Extensions\ExtensionRequest;
use Illuminate\Http\Request;

class ApiController extends \Nuwave\Lighthouse\Support\Http\Controllers\GraphQLController
{
    public function query(Request $request)
    {
        // If the request is a 0-indexed array, we know we are dealing with a batched query
        $batched = isset($request->toArray()[0]) && config('lighthouse.batched_queries', true);

        $this->extensionRegistry->requestDidStart(
            new ExtensionRequest($request, $batched)
        );

        $response = $batched
            ? $this->executeBatched($request)
            : $this->execute($request);

        $response = $this->extensionRegistry->willSendResponse($response);
        $response = $response instanceof Response ? $response : response($response);

        return $response;
    }
}
