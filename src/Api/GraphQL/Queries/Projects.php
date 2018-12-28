<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Api\GraphQL\Utils;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;

class Projects
{
    public function all(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex = codex();
        /** @var \Codex\Projects\ProjectCollection $projects */
        $projects = $codex->getProjects()->makeAll();
        $projects = $constraints->applyConstraints($projects);

        $show = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $projects->show($show);
        return $projects;
    }

    public function get($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex      = codex();
        $defaultKey = $codex->getProjects()->getDefaultKey();
        $key        = data_get($args, 'key', $defaultKey);
        if ( ! $codex->hasProject($key)) {
            throw new Error("Project [{$key}] was not found");
        }
        $project = $codex->getProject($key);
        $show    = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $project->show($show);
        return $project;
    }
}
