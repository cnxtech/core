<?php

namespace Codex\Http\Controllers;

use Codex\Commands\GetBackendData;
use Codex\Hooks;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;

class DocumentController extends Controller
{
    use DispatchesJobs;

    public function getBackendData()
    {
        $content = $this->dispatch(new GetBackendData());
        $response = response($content, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
        ]);
        $response = Hooks::waterfall('controller.web.backend_data', $response);
        return $response;
    }

    public function getDocument($projectKey = null, $revisionKey = null, $documentKey = null)
    {
        $codex = codex();

        if (null === $projectKey) {
            return redirect($codex->url($codex->getProjects()->getDefaultKey()));
        }
        abort_if(! $codex->hasProject($projectKey), 404);

        $project = $codex->getProject($projectKey);

        if (null === $revisionKey) {
            return redirect($codex->url($projectKey, $project->getRevisions()->getDefaultKey()));
        }
        abort_if(! $project->hasRevision($revisionKey), 404);

        $revision = $project->getRevision($revisionKey);

        if (null === $documentKey) {
            return redirect($codex->url($projectKey, $revisionKey, $revision->getDocuments()->getDefaultKey()));
        }
        abort_if(! $revision->hasDocument($documentKey), 404);

        $document = $revision->getDocument($documentKey);
        $content  = $document->getContent();


        if (Hooks::run('controller.web.document', [ $project, $revision, $document ], true)) {
            view()->share(compact('codex', 'project', 'revision', 'document', 'content'));
            $view = view($codex[ 'http.documentation_view' ], compact('content'));
            Hooks::run('controller.web.view', [ $view ]);
            return $view;
        }
    }
}
