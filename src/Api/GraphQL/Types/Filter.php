<?php

namespace Codex\Api\GraphQL\Types;

use GraphQL\Type\Definition\ResolveInfo;

class Filter
{

    public function resolve($rootValue, array $args, $context, ResolveInfo $info)
    {
        $codex    = codex();
        return [

        ];
    }

    public function resolveType()
    {
        return [];
    }
}
