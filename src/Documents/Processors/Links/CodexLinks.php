<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author    Robin Radic
 * @license   https://codex-project.mit-license.org MIT License
 */

namespace Codex\Documents\Processors\Links;


use Codex\Contracts\Documents\Document;
use Codex\Contracts\Projects\Project;
use Codex\Contracts\Revisions\Revision;

class CodexLinks
{
    /**
     * project method.
     *
     * @CA\Link(name="project", description="Creates a link to a project")
     *
     * @param \Codex\Documents\Processors\Links\Link $link
     *
     * @throws \Codex\Exceptions\Exception
     */
    public function project(Link $link)
    {
        $this->prepare($link);
        $projects = codex()->getProjects();
        $key      = $link->param(0);
        if (false === $projects->has($key)) {
            return;
        }
        $this->setUrl($link, $key);
        $this->handleModifiers('project', $link);
    }

    /**
     * revision method.
     *
     * @CA\Link(name="revision", description="Creates a link to a revision")
     *
     * @param \Codex\Documents\Processors\Links\Link $link
     *
     * @throws \Codex\Exceptions\Exception
     */
    public function revision(Link $link)
    {
        $this->prepare($link);
        $i = $link->countParameters();
        if (1 === $i) {
            $this->setUrl($link, $link->getProject(), $link->param(0));
        } elseif (2 === $i) {
            $this->setUrl($link, $link->param(0), $link->param(1));
        }
        $this->handleModifiers('revision', $link);
    }

    /**
     * document method.
     *
     * @CA\Link("document", description="Creates a link to a document")
     *
     * @param \Codex\Documents\Processors\Links\Link $link
     *
     * @throws \Codex\Exceptions\Exception
     */
    public function document(Link $link)
    {
        $this->prepare($link);
        $i = $link->countParameters();
        if (1 === $i) {
            $this->setUrl($link, $link->getProject(), $link->getRevision(), $link->param(0));
        } elseif (2 === $i) {
            $this->setUrl($link, $link->getProject(), $link->param(0), $link->param(1));
        } elseif (3 === $i) {
            $this->setUrl($link, $link->param(0), $link->param(1), $link->param(2));
        }
        $this->handleModifiers('document', $link);
    }

    protected function prepare(Link $link)
    {
        // Replace element tag
        $replace = $link->getProcessor()->config('replace_tag', false);
        if ($replace !== false && is_string($replace)) {
            $link->replaceElement($replace, $link->getElement()->textContent);
        }
    }

    protected $props = [];

    protected function setUrl(Link $link, $project = null, $revision = null, $document = null)
    {
        if ($project instanceof Project) {
            $project = $project->getKey();
        }
        if ($revision instanceof Revision) {
            $revision = $revision->getKey();
        }
        if ($document instanceof Document) {
            $document = $document->getKey();
        }
        $this->props = array_merge($this->props, compact('project', 'revision', 'document'));
        $link->setElementUrl(codex()->url($project, $revision, $document));
    }

    protected function handleModifiers(string $type, Link $link)
    {
        $action = null;
        if ($link->hasModifier('tooltip')) {
            $action = 'tooltip';
        } elseif ($link->hasModifier('popover')) {
            $action = 'popover';
        } elseif ($link->hasModifier('modal')) {
            $action = 'modal';
        }

        $to          = $link->getUrl()->getPath();
        $styling     = $link->hasModifier('!styling') ? false : true;
        $icon        = $link->hasModifier('!icon') ? false : true;
        $this->props = array_merge($this->props, compact('type', 'action', 'to', 'styling', 'icon'));

        // Add modifiers as data-* attributes and as json string in data-prop
//        $el = $link->getElement();
        $el = $link->replaceElement('c-link', $link->getElement()->textContent, [ 'props' => json_encode($this->props, JSON_UNESCAPED_SLASHES) ]);
//        foreach ($this->props as $key => $value) {
//            if ($value === null) {
//                continue;
//            }
//            $el->setAttribute('data-' . $key, (string)$value);
//        }
    }
}
