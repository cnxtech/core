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
        $projects = codex()->getProjects();
        $key      = $link->param(0);
        if (false === $projects->has($key)) {
            return;
        }
        $link->setElementUrl(codex()->url($key));
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
        $i = $link->countParameters();
        if (1 === $i) {
            $link->setElementUrl(codex()->url($link->getProject(), $link->param(0)));
        } elseif (2 === $i) {
            $link->setElementUrl(codex()->url($link->param(0), $link->param(1)));
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
        $i = $link->countParameters();
        if (1 === $i) {
            $link->setElementUrl(codex()->url($link->getProject(), $link->getRevision(), $link->param(0)));
        } elseif (2 === $i) {
            $link->setElementUrl(codex()->url($link->getProject(), $link->param(0), $link->param(1)));
        } elseif (3 === $i) {
            $link->setElementUrl(codex()->url($link->param(0), $link->param(1), $link->param(2)));
        }
        $this->handleModifiers('document', $link);
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

        $to        = $link->getUrl()->getPath();
        $styling   = $link->hasModifier('!styling') ? false : true;
        $icon      = $link->hasModifier('!icon') ? false : true;
        $modifiers = compact('type', 'action', 'to', 'styling', 'icon');
        $el        = $link->getElement();
        foreach ($modifiers as $key => $value) {
            $el->setAttribute('data-' . $key, (string) $value);
        }

//        $link->replaceElement('c-link', $link->getElement()->textContent, [
//            'props' => json_encode(compact('type', 'action', 'to', 'styling', 'icon')),
//        ]);
    }
}
