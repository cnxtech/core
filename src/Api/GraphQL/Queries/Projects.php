<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Projects
{
    public function resolve(QueryConstraints $constraints, ResolveInfo $info, array $args = [])
    {
        $codex    = codex();
        /** @var \Codex\Projects\ProjectCollection $projects */
        $projects = $codex->getProjects()->makeAll();
        $projects = $constraints->applyConstraints($projects);
        $show     = Utils::transformSelectionToShow($info->getFieldSelection(5));
        return $projects->getGraphSelection($show);
    }
}
