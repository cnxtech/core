<?php

namespace Codex\Http\Controllers;

use Illuminate\Routing\Controller;

class DocumentController extends Controller
{

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

        view()->share(compact('codex', 'project', 'revision', 'document', 'content'));

        return view($codex[ 'http.documentation_view' ], compact('content'));
    }
}
