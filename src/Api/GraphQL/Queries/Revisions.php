<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Revisions #extends BaseQuery
{
    /**
     * resolve method
     *
     * @param \Codex\Api\GraphQL\Directives\QueryConstraints $constraints
     * @param \GraphQL\Type\Definition\ResolveInfo           $info
     * @param array                                          $args
     *
     * @return mixed
     */
    public function resolve(QueryConstraints $constraints, ResolveInfo $info, array $args = [])
    {
        $codex      = codex();
        $projectKey = data_get($args, 'projectKey', codex()->getProjects()->getDefault());
        $project    = $codex->getProject($projectKey);
        $revisions  = $project->getRevisions()->makeAll();
        $revisions  = $constraints->applyConstraints($revisions);
        $show       = Utils::transformSelectionToShow($info->getFieldSelection(2));
        $data = $revisions->getGraphSelection($show);
        return $data;
    }
}
