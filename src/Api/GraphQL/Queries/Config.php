<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Config
{
    public function resolve($rootValue, array $args, $context, ResolveInfo $info)
    {
        $codex     = codex();
        $config    = collect(config('app'))->only([ 'fallback_locale', 'locale', 'timezone', 'url', 'debug', 'env', 'name' ]);
        $selection = $info->getFieldSelection(2);
        $show      = Utils::transformSelectionToShow($selection);
        $data      = $config->only($show);
        return $data;
    }

}
