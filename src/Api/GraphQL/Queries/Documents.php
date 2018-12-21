<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Documents
{
    public function resolve(QueryConstraints $constraints, ResolveInfo $info, array $args = [])
    {
        $codex       = codex();
        $projectKey  = data_get($args, 'projectKey', codex()->getProjects()->getDefault());
        $project     = $codex->getProject($projectKey);
        $revisionKey = data_get($args, 'revisionKey', $project->getRevisions()->getDefaultKey());
        $revision    = $project->getRevision($revisionKey);
        $documents   = $revision->getDocuments()->makeAll(); // makeAll() ?
        $documents   = $constraints->applyConstraints($documents);
        $show        = Utils::transformSelectionToShow($info->getFieldSelection(2));
        $data        = $documents->getGraphSelection($show);
        return $data;
    }

}
