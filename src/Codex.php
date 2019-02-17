<?php

namespace Codex;

use Codex\Addons\AddonCollection;
use Codex\Addons\Extensions\ExtensionCollection;
use Codex\Api\GraphQL\GraphQL;
use Codex\Attributes\AttributeDefinitionRegistry;
use Codex\Contracts\Documents\Document;
use Codex\Contracts\Mergable\ParentInterface;
use Codex\Contracts\Projects\Project;
use Codex\Contracts\Revisions\Revision;
use Codex\Exceptions\NotFoundException;
use Codex\Mergable\Commands\ProcessAttributes;
use Codex\Mergable\Concerns\HasChildren;
use Codex\Mergable\Model;
use Codex\Projects\ProjectCollection;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * This is the class Codex.
 *
 * @package Codex
 * @author  Robin Radic
 * @method \Codex\Projects\ProjectCollection getChildren()
 */
class Codex extends Model implements ParentInterface
{
    use DispatchesJobs;
    use HasChildren {
        _setChildrenProperty as setChildren;
    }

    protected $children;

    /** @var \Illuminate\Contracts\Container\Container */
    protected $container;

    /** @var \Codex\Attributes\AttributeDefinitionRegistry */
    protected $registry;

    /** @var \Codex\Addons\AddonCollection */
    protected $addons;

    /** @var \Codex\Addons\Extensions\ExtensionCollection */
    protected $extensions;

    /** @var \Codex\Api\GraphQL\GraphQL */
    protected $api;

    /**
     * Codex constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository       $config
     * @param \Codex\Projects\ProjectCollection             $projects
     * @param \Codex\Attributes\AttributeDefinitionRegistry $registry
     * @param \Codex\Addons\AddonCollection                 $addons
     * @param \Codex\Addons\Extensions\ExtensionCollection  $extensions
     * @param \Codex\Api\GraphQL\GraphQL                    $api
     * @param \Codex\Contracts\Log\Log                      $log
     */
    public function __construct(
        Config $config,
        ProjectCollection $projects,
        AttributeDefinitionRegistry $registry,
        AddonCollection $addons,
        ExtensionCollection $extensions,
        GraphQL $api
    )
    {
        $this->registry   = $registry;
        $this->addons     = $addons;
        $this->extensions = $extensions;
        $this->api        = $api;


        $this->setChildren($projects->setParent($this));
        $attributes = array_except($config->get('codex', []), [ 'projects', 'revisions', 'documents' ]);
        $group      = $registry->resolveGroup('codex');
        $attributes = $this->dispatch(new ProcessAttributes($group, $attributes));
        $this->initialize($attributes, $group)->rehide();
        $this->addGetMutator('urls', 'getUrls', true, true);
        $this->addGetMutator('changes', 'getChanges', true, true);
    }

    public function getUrls()
    {
        $routeMap = [
            'root'          => 'codex',
            'api'           => 'codex.api',
            'documentation' => 'codex.documentation',
        ];
        $routeMap = Hooks::waterfall('codex.urls.map', $routeMap, [ $this ]);
        $urls = collect($routeMap)->map(function ($routeName) {
            try {
                return url()->route($routeName, [], false);
            }
            catch (\Exception $e) {
                $route      = app()->make('router')->getRoutes()->getByName($routeName);
                $parameters = [];
                foreach ($route->parameterNames() as $name) {
                    $parameters[ $name ] = '__' . $name . '__';
                }
                return url()->route($routeName, $parameters, false);
            }
        })->toArray();
        $urls = Hooks::waterfall('codex.urls.mapped', $urls, [ $this ]);
        return $urls;
    }

    public function url($projectKey = null, $revisionKey = null, $documentKey = null, $absolute = true)
    {
        if ($projectKey instanceof Project) {
            $projectKey = $projectKey->getKey();
        }
        if ($revisionKey instanceof Revision) {
            $revisionKey = $revisionKey->getKey();
        }
        if ($documentKey instanceof Document) {
            $documentKey = $documentKey->getKey();
        }
        return route('codex.documentation', compact('projectKey', 'revisionKey', 'documentKey'), $absolute);
    }

    /**
     * projects method
     *
     * @return \Codex\Mergable\EloquentCollection|\Codex\Contracts\Projects\Project[]
     */
    public function projects()
    {
        return $this->getProjects()->toRelationship();
    }

    /**
     * getProjects method
     *
     * @return \Codex\Projects\ProjectCollection|\Codex\Projects\Project[]
     */
    public function getProjects()
    {
        return $this->getChildren()->resolve();
    }

    /**
     * getProject method
     *
     * @param $key
     *
     * @return \Codex\Contracts\Projects\Project
     */
    public function getProject($key)
    {
        return $this->getProjects()->get($key);
    }

    /**
     * hasProject method
     *
     * @param $key
     *
     * @return bool
     */
    public function hasProject($key)
    {
        return $this->getProjects()->has($key);
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function getAddons()
    {
        return $this->addons;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function getApi()
    {
        return $this->api;
    }

    /** @return \Codex\Contracts\Log\Log */
    public function getLog()
    {
        return $this->getContainer()->make('codex.log');
    }

    public function getDocsPath()
    {
        return $this[ 'paths.docs' ];
    }

    /**
     * Shorthand method for getting projects, refs or documents.
     *
     * **Syntax:**
     * `{project?}/{$ref?}::{documentPath?}`
     *
     * **Modifiers:**
     * - `*` The collection (projects, refs or documents)
     * - `!` The default of the collection (project, def or document)
     *
     * **Syntax examples:**
     * `codex/master::getting-started/installation`
     *
     * @example
     * $projects    = codex()->get('*'); # Codex\Entities\Projects
     * $project     = codex()->get('!'); # Codex\Entities\Project (default)
     * $project     = codex()->get('codex'); # Codex\Entities\Project
     * $refs        = codex()->get('codex/*'); # Codex\Entities\Refs
     * $ref         = codex()->get('codex/!'); # Codex\Entities\Ref (default ref)
     * $ref         = codex()->get('codex/master'); # Codex\Entities\Ref
     * $ref         = codex()->get('codex/1.0.0'); # Codex\Entities\Ref
     * $documents   = codex()->get('codex::index'); # Codex\Entities\Document (from default ref)
     * $documents   = codex()->get('codex/master::*'); # Codex\Entities\Documents
     * $document    = codex()->get('codex/master::!'); # Codex\Entities\Document (default document)
     * $document    = codex()->get('codex/master::index'); # Codex\Entities\Document
     * $document    = codex()->get('!/!::!'); # Codex\Entities\Document
     * $document    = codex()->get('codex/master::develop/hooks'); # Codex\Entities\Document
     *
     * @param string $query The query to run
     *
     * @return \Codex\Documents\Document|\Codex\Documents\DocumentCollection|\Codex\Projects\Project|\Codex\Projects\ProjectCollection|\Codex\Revisions\Revision|\Codex\Revisions\RevisionCollection
     *
     * @throws \Codex\Exceptions\NotFoundException
     */
    public function get($query)
    {
        // project/ref::path/to/document

        $segments  = explode('::', $query);
        $psegments = explode('/', $segments[ 0 ]);

        $projectKey  = $psegments[ 0 ];
        $revisionKey = isset($psegments[ 1 ]) ? $psegments[ 1 ] : false;
        $documentKey = isset($segments[ 1 ]) ? $segments[ 1 ] : false;

        // Projects / Project
        if ('*' === $projectKey) {
            return $this->getProjects();
        }
        if ('!' === $projectKey) {
            $project = $this->getProjects()->getDefault();
        } elseif ($this->hasProject($projectKey)) {
            $project = $this->getProject($projectKey);
        } else {
            throw NotFoundException::project($projectKey);
        }

        // Revisions / Revision
        if (false === $revisionKey) {
            if (false === $documentKey) {
                return $project;
            }
            $revisionKey = '!'; // make it so that the default ref will be chosen when using get('codex::path/to/document')
        }
        if ('*' === $revisionKey) {
            return $project->getRevisions();
        }
        if ('!' === $revisionKey) {
            $revision = $project->getRevisions()->getDefault();
        } elseif ($project->hasRevision($revisionKey)) {
            $revision = $project->getRevision($revisionKey);
        } else {
            throw NotFoundException::revision($revisionKey);
        }
        if (false === $documentKey) {
            return $revision;
        }

        // Documents / Document
        if ('*' === $documentKey) {
            return $revision->getDocuments();
        }
        if ('!' === $documentKey) {
            return $revision->getDocuments()->getDefault();
        }
        if (false === $revision->hasDocument($documentKey)) {
            throw NotFoundException::document($documentKey);
        }
        return $revision->getDocument($documentKey);
    }

    public function log(string $string, $message, array $context = [])
    {
        $this->getLog()->log($string, $message, $context);
        return $this;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = true;
        return $this;
    }

}
