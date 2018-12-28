<?php

namespace Codex\Api\GraphQL;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Codex;
use Codex\Exceptions\NotFoundException;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;

class CodexQueries
{
    public function resolve($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex = codex();
        $show  = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $codex->show($show);
        return $codex;
    }

    public function projects(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex = $rootValue instanceof Codex ? $rootValue : codex();
        /** @var \Codex\Projects\ProjectCollection $projects */
        $projects = $codex->getProjects()->makeAll();
        $projects = $constraints->applyConstraints($projects);

        $show = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $projects->show($show);
        return $projects;
    }

    public function project($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex      = $rootValue instanceof Codex ? $rootValue : codex();
        $defaultKey = $codex->getProjects()->getDefaultKey();
        $key        = data_get($args, 'key', $defaultKey);
        if ( ! $codex->hasProject($key)) {
            throw NotFoundException::project($key)->toApiError();
        }
        $project = $codex->getProject($key);
        $show    = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $project->show($show);
        return $project;
    }

    public function revisions(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        if ($rootValue instanceof \Codex\Contracts\Projects\Project) {
            $project = $rootValue;
        } else {
            $codex      = codex();
            $projectKey = data_get($args, 'projectKey', $codex->getProjects()->getDefault());
            $project    = $codex->getProject($projectKey);
            if ( ! $codex->hasProject($projectKey)) {
                throw NotFoundException::project($projectKey)->toApiError();
            }
        }

        $revisions = $project->getRevisions()->makeAll();
        $revisions = $constraints->applyConstraints($revisions);
        $show      = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $revisions->show($show);
        return $revisions;
    }

    public function revision($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        if ($rootValue instanceof \Codex\Contracts\Projects\Project) {
            $project = $rootValue;
        } else {
            $codex      = codex();
            $projectKey = data_get($args, 'projectKey', $codex->getProjects()->getDefault());
            if ( ! $codex->hasProject($projectKey)) {
                throw NotFoundException::project($projectKey)->toApiError();
            }
            $project = $codex->getProject($projectKey);
        }
        $revisionKey = data_get($args, 'revisionKey', $project->getRevisions()->getDefaultKey());
        if ( ! $project->hasRevision($revisionKey)) {
            throw NotFoundException::revision($revisionKey)->toApiError();
        }
        $revision = $project->getRevision($revisionKey);
        $show     = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $revision->show($show);
        return $revision;
    }


    public function documents(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        if ($rootValue instanceof \Codex\Contracts\Revisions\Revision) {
            $revision = $rootValue;
        } else {
            $codex      = codex();
            $projectKey = data_get($args, 'projectKey', $codex->getProjects()->getDefault());
            if ( ! $codex->hasProject($projectKey)) {
                throw NotFoundException::project($projectKey)->toApiError();
            }
            $project     = $codex->getProject($projectKey);
            $revisionKey = data_get($args, 'revisionKey', $project->getRevisions()->getDefaultKey());
            if ( ! $project->hasRevision($revisionKey)) {
                throw NotFoundException::revision($revisionKey)->toApiError();
            }
            $revision = $project->getRevision($revisionKey);
        }
        $documents = $revision->getDocuments()->makeAll(); // makeAll() ?
        $documents = $constraints->applyConstraints($documents);
        $show      = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $documents->show($show);
        return $documents;
    }


    public function document($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        if ($rootValue instanceof \Codex\Contracts\Revisions\Revision) {
            $revision = $rootValue;
        } else {
            $codex      = codex();
            $projectKey = data_get($args, 'projectKey', $codex->getProjects()->getDefault());
            if ( ! $codex->hasProject($projectKey)) {
                throw NotFoundException::project($projectKey)->toApiError();
            }
            $project     = $codex->getProject($projectKey);
            $revisionKey = data_get($args, 'revisionKey', $project->getRevisions()->getDefaultKey());
            if ( ! $project->hasRevision($revisionKey)) {
                throw NotFoundException::revision($revisionKey)->toApiError();
            }
            $revision = $project->getRevision($revisionKey);
        }
        $documentKey = data_get($args, 'documentKey', $revision->getDocuments()->getDefaultKey());
        if ( ! $revision->hasDocument($documentKey)) {
            throw NotFoundException::document($documentKey)->toApiError();
        }
        $document = $revision->getDocument($documentKey);
        $show     = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $document->show($show);
        return $document;
    }
}
