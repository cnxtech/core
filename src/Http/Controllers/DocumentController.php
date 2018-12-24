<?php

namespace Codex\Http\Controllers;

use Illuminate\Routing\Controller;

class DocumentController extends Controller
{

    public function getDocument($projectKey = null, $revisionKey = null, $documentPath = null)
    {
        $codex = codex();

        if (null === $projectKey) {
            return redirect($codex->url($codex->getProjects()->getDefaultKey()));
        }
        $project = $codex->getProject($projectKey);

        if (null === $revisionKey) {
            return redirect($codex->url($projectKey, $project->getRevisions()->getDefaultKey()));
        }
        $revision = $project->getRevision($revisionKey);

        if (null === $documentPath) {
            return redirect($codex->url($projectKey, $revisionKey, $revision->getDocuments()->getDefaultKey()));
        }
        $document = $revision->getDocument($documentPath);

        $content = $document->getContent();

        view()->share(compact('codex', 'project', 'revision', 'document', 'content'));

        return view($codex[ 'http.documentation_view' ], compact('content'));
    }
}
