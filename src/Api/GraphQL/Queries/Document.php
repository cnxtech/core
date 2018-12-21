<?php

namespace Codex\Api\GraphQL\Queries;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Api\GraphQL\Utils;
use GraphQL\Type\Definition\ResolveInfo;

class Document
{
    public function resolve(QueryConstraints $constraints, ResolveInfo $info, array $args = [])
    {
        $codex       = codex();
        $projectKey  = data_get($args, 'projectKey', codex()->getProjects()->getDefault());
        $project     = $codex->getProject($projectKey);
        $revisionKey = data_get($args, 'revisionKey', $project->getRevisions()->getDefaultKey());
        $revision    = $project->getRevision($revisionKey);
        $documentKey = data_get($args, 'documentKey', $revision->getDocuments()->getDefaultKey());
        $document    = $revision->getDocument($documentKey);
        $document    = $constraints->applyConstraints($document);
        $show        = Utils::transformSelectionToShow($info->getFieldSelection(2));
        $data        = $document->getGraphSelection($show);
        return $data;
    }
}
