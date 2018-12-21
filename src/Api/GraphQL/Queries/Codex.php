<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Codex
{
    /** @var ResolveInfo */
    protected $info;

    public function resolve($rootValue, array $args, $context, ResolveInfo $info)
    {
        $this->info = $info;
//        Visitor::visit($info->operation, [
//            'Field' => [
//                'enter' => function ($node, $key, $parent, $path, $ancestors) {
//                    $args = func_get_args();
//                    $name = $node->name->value;
//                    if ($name === 'projects') {
//                        $queryType = $this->info->schema->getQueryType();
//                        $fields    = $queryType->getFields();
//                        $a         = 'a';
//                    }
//                },
//                'leave' => function ($node, $key, $parent, $path, $ancestors) {
//                },
//            ],
//        ]);

        $selection = $info->getFieldSelection(5);
        $show      = Utils::transformSelectionToShow($selection);
        $data      = codex()->getGraphSelection($show);

        return $data;
    }
}
