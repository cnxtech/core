<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Revisions #extends BaseQuery
{
    public function all(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex = codex();
        if ($rootValue instanceof \Codex\Contracts\Projects\Project) {
            $project = $rootValue;
        } else {
            $projectKey = data_get($args, 'projectKey', codex()->getProjects()->getDefault());
            $project    = $codex->getProject($projectKey);
        }
        $revisions = $project->getRevisions()->makeAll();
        $revisions = $constraints->applyConstraints($revisions);
        $show      = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(2));
        $data      = $revisions->getGraphSelection($show);
        return $data;
    }


    public function get($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex       = codex();
        $projectKey  = data_get($args, 'projectKey', codex()->getProjects()->getDefault());
        $project     = $codex->getProject($projectKey);
        $revisionKey = data_get($args, 'revisionKey', $project->getRevisions()->getDefaultKey());
        $revision    = $project->getRevision($revisionKey);
        $show        = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(2));
        $data        = $revision->getGraphSelection($show);
        return $data;
    }

}
