<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Project
{
    public function resolve(QueryConstraints $constraints, ResolveInfo $info, array $args = [])
    {
        $codex      = codex();
        $key        = data_get($args, 'key', null);
        $defaultKey = $codex->getProjects()->getDefault();

        if ($key === null) {
            $projects = $codex->getProjects()->makeAll();
            $projects = $constraints->applyConstraints($projects);
            $project  = $projects->first();
        }
        if (! isset($project) ) {
            $project = $codex->hasProject($key) ? $codex->getProject($key) : $codex->hasProject($defaultKey);
        }

        $show = Utils::transformSelectionToShow($info->getFieldSelection(2));
        return $project->getGraphSelection($show);
    }

}
