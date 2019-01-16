<?php

namespace Codex\Api\GraphQL\Extensions;

use GraphQL\Executor\ExecutionResult;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Extensions\ExtensionRequest;

class ETagExtension extends \Nuwave\Lighthouse\Schema\Extensions\GraphQLExtension
{
    public function manipulateSchema(DocumentAST $documentAST)
    {
//        $documentAST->typeExtensions()[0]->
        return $documentAST;
    }

    /** @var ExtensionRequest */
    protected $request;

    public function requestDidStart(ExtensionRequest $request)
    {
        $this->request = $request;
        return;
    }

    public function batchedQueryDidStart($index)
    {
        return;
    }

    protected $hasError = false;

    public function batchedQueryDidEnd(ExecutionResult $result, $index)
    {
        if ( ! empty($result->errors)) {
            $this->hasError = true;
            return;
        }
    }

    public function willSendResponse(array $data, \Closure $next)
    {
        if ($this->hasError ) {
            return $next($data);
        }
        $data         = $next($data);
        $request      = $this->request->request();
        $response     = response()->make($data);
        $resourceETag = md5(json_encode($data));
        $maxAge       = (60 * 60) * 25;
        $response->setCache([
            'etag'     => $resourceETag,
            'max_age'  => $maxAge,
            's_maxage' => $maxAge,
            'public'   => true,
        ]);

        $etags = $request->getETags();
        foreach ($etags as $etag) {
            $etag = str_replace('"', '', $etag);
            if ($etag === $resourceETag) {
                return $response->setNotModified();
            }
        }

        $etag = str_replace('"', '', $request->headers->get('if-match'));
        if ($etag && $etag === $resourceETag) {
            return $response->setNotModified();
        }
        return $response;
    }


    public static function name()
    {
        return 'test-graph';
    }

    public function jsonSerialize()
    {
        return [ 'cache' => true ];
    }
}
