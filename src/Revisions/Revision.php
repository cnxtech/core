<?php

namespace Codex\Revisions;

use Codex\Concerns;
use Codex\Contracts\Models\ChildInterface;
use Codex\Contracts\Models\ParentInterface;
use Codex\Contracts\Projects\Project;
use Codex\Contracts\Revisions\Revision as RevisionContract;
use Codex\Documents\DocumentCollection;
use Codex\Hooks;
use Codex\Models\Concerns\HasChildren;
use Codex\Models\Concerns\HasParent;
use Codex\Models\Model;
use Codex\Support\DB;

/**
 * This is the class Revision.
 *
 * @package Codex\Revisions
 * @author  Robin Radic
 * @method \Codex\Contracts\Projects\Project getParent()
 * @method \Codex\Documents\DocumentCollection getChildren()
 */
class Revision extends Model implements RevisionContract, ChildInterface, ParentInterface
{
    use Concerns\HasFiles;
    use Concerns\HasCodex;
    use HasParent {
        _setParentAsProperty as setParent;
    }
    use HasChildren {
        _setChildrenProperty as setChildren;
    }
    const DEFAULTS_PATH = 'codex.revisions';

    protected $parent;

    /** @var \Codex\Documents\DocumentCollection */
    protected $children;

    /** @var string */
    protected $configFilePath;

    /**
     * Revision constructor.
     *
     * @param array                               $attributes
     * @param \Codex\Documents\DocumentCollection $documents
     */
    public function __construct(array $attributes, Project $project, DocumentCollection $documents)
    {
        DB::startMeasure($project . ':revision:' . $attributes[ 'key' ]);
        $this->setChildren($documents->setParent($this));
        $registry   = $this->getCodex()->getRegistry()->resolve('revisions');
        $attributes = Hooks::waterfall('revision.initialize', $attributes, [ $registry, $this ]);
        $this->initialize($attributes, $registry);
        $this->addGetMutator('inherits', 'getInheritKeys', true, true);
        $this->addGetMutator('changes', 'getChanges', true, true);
//        foreach($this->getInheritKeys() as $key){
//            if($this->getParent()->hasGetMutator($key)){
//                $this->getParent()->getMutators
//            }
//        }
        Hooks::run('revision.initialized', [ $this ]);

    }

    public function url($documentKey = null, $absolute = true)
    {
        return $this->getCodex()->url($this->getProject()->getKey(), $this->getKey(), $documentKey, $absolute);
    }

    public function path(...$parts)
    {
        return path_njoin($this->getKey(), ...$parts);
    }

    public function documents()
    {
        return $this->getDocuments()->toRelationship();
    }

    /**
     * getProject method
     *
     * @return \Codex\Contracts\Projects\Project
     */
    public function getProject()
    {
        return $this->getParent();
    }

    /**
     * @return \Codex\Documents\DocumentCollection
     */
    public function getDocuments()
    {
        return $this->getChildren()->resolve();
    }

    /**
     * getDocument method
     *
     * @param $key
     *
     * @return \Codex\Documents\Document
     */
    public function getDocument($key)
    {
        return $this->getDocuments()->get($key);
    }

    /**
     * hasDocument method
     *
     * @param $key
     *
     * @return bool
     */
    public function hasDocument($key)
    {
        return $this->getDocuments()->has($key);
    }

    public function getDefaultDocumentKey()
    {
        return $this->getDocuments()->getDefaultKey();
    }

    public function setConfigFilePath($configFilePath)
    {
        $this->configFilePath = $configFilePath;
        return $this;
    }

    public function getConfigFilePath()
    {
        return $this->configFilePath;
    }

    public function getLastModified()
    {
        return $this->getFiles()->lastModified($this->configFilePath);
    }
}
