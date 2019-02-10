<?php /** @noinspection ALL */

namespace Codex\Api\GraphQL;

use Codex\Api\GraphQL\Directives\QueryConstraints;
use Codex\Codex;
use Codex\Exceptions\NotFoundException;
use Codex\Hooks;
use Codex\Mergable\Commands\GetChangedAttributes;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CodexQueries
{
    use DispatchesJobs;

    public function resolve($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex = codex();
        $show  = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $codex->show($show);
        Hooks::run('api.query.resolve', [ $codex, $show ]);
        return $codex;
    }

    /** @return \Codex\Contracts\Projects\Project */
    protected function getProject($key = null)
    {
        if ($key === null) {
            $key = codex()->getProjects()->getDefaultKey();
        }
        if ( ! codex()->hasProject($key)) {
            throw NotFoundException::project($key)->toApiError();
        }
        $project = codex()->getProject($key);
        return Hooks::waterfall('api.queries.get.project', $project, [ $key ]);
    }

    /** @return \Codex\Contracts\Revisions\Revision */
    protected function getRevision($projectKey = null, $revisionKey = null)
    {
        $project = $this->getProject($projectKey);
        if ( ! $project->hasRevision($revisionKey)) {
            throw NotFoundException::revision($revisionKey)->toApiError();
        }
        if ($revisionKey === null) {
            $revisionKey = $project->getRevisions()->getDefaultKey();
        }
        $revision = $project->getRevision($revisionKey);
        return Hooks::waterfall('api.queries.get.revision', $revision, [ $projectKey, $revisionKey ]);
    }

    /** @return \Codex\Contracts\Revisions\Revision */
    protected function getDocument($projectKey = null, $revisionKey = null, $documentKey = null)
    {
        $revision = $this->getRevision($projectKey, $revisionKey);
        if ( ! $revision->hasDocument($documentKey)) {
            throw NotFoundException::document($documentKey)->toApiError();
        }
        if ($documentKey === null) {
            $documentKey = $revision->getDocuments()->getDefaultKey();
        }
        $document = $revision->getDocument($documentKey);
        return Hooks::waterfall('api.queries.get.document', $document, [ $projectKey, $revisionKey, $documentKey ]);
    }

    public function projects(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $codex = $rootValue instanceof Codex ? $rootValue : codex();
        /** @var \Codex\Projects\ProjectCollection $projects */
        $projects = $codex->getProjects()->makeAll();
        $projects = $constraints->applyConstraints($projects);

        $show = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $projects->show($show);
        return Hooks::waterfall('api.query.projects', $projects, [ $rootValue, $args, $context, $resolveInfo ]);
    }

    public function project($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $key     = data_get($args, 'key');
        $project = $this->getProject($key);
        $show    = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $project->show($show);
        return Hooks::waterfall('api.queries.project', $project, [ $rootValue, $args, $context, $resolveInfo ]);
    }

    public function revisions(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $projectKey = data_get($args, 'projectKey');
        $project    = $this->getProject($projectKey);
        $revisions  = $project->getRevisions()->makeAll();
        $revisions  = $constraints->applyConstraints($revisions);
        $show       = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $revisions->show($show);
        return Hooks::waterfall('api.queries.revisions', $revisions, [ $rootValue, $args, $context, $resolveInfo ]);
    }

    public function revision($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $projectKey  = data_get($args, 'projectKey');
        $revisionKey = data_get($args, 'revisionKey');
        $revision    = $this->getRevision($projectKey, $revisionKey);
        $show        = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $revision->show($show);
        return Hooks::waterfall('api.queries.revision', $revision, [ $rootValue, $args, $context, $resolveInfo ]);
    }


    public function documents(QueryConstraints $constraints, $rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $projectKey  = data_get($args, 'projectKey');
        $revisionKey = data_get($args, 'revisionKey');
        $revision    = $this->getRevision($projectKey, $revisionKey);
        $documents   = $revision->getDocuments()->makeAll(); // makeAll() ?
        $documents   = $constraints->applyConstraints($documents);
        $show        = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $documents->show($show);
        return Hooks::waterfall('api.queries.documents', $documents, [ $rootValue, $args, $context, $resolveInfo ]);
    }


    public function document($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $projectKey  = data_get($args, 'projectKey');
        $revisionKey = data_get($args, 'revisionKey');
        $documentKey = data_get($args, 'documentKey');
        $document    = $this->getDocument($projectKey, $revisionKey, $documentKey);
        $show        = Utils::transformSelectionToShow($resolveInfo->getFieldSelection(0));
        $document->show($show);
        return Hooks::waterfall('api.queries.document', $document, [ $rootValue, $args, $context, $resolveInfo ]);
    }

    public function diff($rootValue, array $args, $context, ResolveInfo $resolveInfo)
    {
        $left = data_get($args, 'left', null);
        if ($left !== null) {
            $left = codex()->get($left);
        }
        $right = data_get($args, 'right', null);
        if ($right !== null) {
            $right = codex()->get($right);
        }
        $data = $this->dispatch(new GetChangedAttributes($left, $right));


        return [ 'attributes' => $data ];
    }
}
