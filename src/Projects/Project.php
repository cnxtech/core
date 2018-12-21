<?php

namespace Codex\Projects;

use Codex\Concerns;
use Codex\Contracts\Mergable\ChildInterface;
use Codex\Contracts\Mergable\ParentInterface;
use Codex\Contracts\Projects\Project as ProjectContract;
use Codex\Mergable\Concerns\HasChildren;
use Codex\Mergable\Concerns\HasParent;
use Codex\Mergable\Model;
use Codex\Revisions\RevisionCollection;
use Illuminate\Contracts\Filesystem\Factory;

/**
 * This is the class Project.
 *
 * @package Codex\Projects
 * @author  Robin Radic
 */
class Project extends Model implements ProjectContract, ChildInterface, ParentInterface
{
    use Concerns\HasFiles;
    use Concerns\HasCodex;
    use HasParent {
        _setParentAsProperty as setParent;
    }
    use HasChildren {
        _setChildrenProperty as setChildren;
    }

    const DEFAULTS_PATH = 'codex.projects';
//    public $mergePaths = [
//        Mergable::CASTS_PATH    => 'codex.projects.casts',
//        Mergable::DEFAULTS_PATH => 'codex.projects.defaults',
//        Mergable::INHERITS_PATH => 'codex.projects.inherits',
//    ];

    /** @var \Codex\Codex */
    protected $parent;

    /** @var \Codex\Revisions\RevisionCollection */
    protected $children;

    /**
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $fsm;


    /**
     * Project constructor.
     *
     * @param array                                    $attributes
     * @param \Codex\Revisions\RevisionCollection      $revisions
     * @param \Illuminate\Contracts\Filesystem\Factory $fsm
     */
    public function __construct(array $attributes, RevisionCollection $revisions, Factory $fsm)
    {
        $this->fsm = $fsm;
        $this->setParent($this->getCodex());
        $this->setChildren($revisions->setParent($this));
        $registry = $this->getCodex()->getAttributeRegistry()->resolveGroup('projects');
        $registry->add($this->primaryKey, $this->keyType);
        $registry->add('default_revision', 'string');
        $this->init($attributes, $registry);
        $this->addGetMutator('default_revision', 'getDefaultRevisionKey', true, true);
    }

    public function revisions()
    {
        return $this->getRevisions()->toRelationship();
    }

    public function setMergedAttributes(array $attributes)
    {
        parent::setMergedAttributes($attributes);
        $this->updateDisk();
        return $this;
    }

    /**
     * newCollection method
     *
     * @param array $models
     *
     * @return \Codex\Projects\ProjectCollection|\Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new ProjectCollection($this->getParent() || codex(), $models);
    }

    /**
     * getRevisions method
     *
     * @return \Codex\Revisions\RevisionCollection
     */
    public function getRevisions()
    {
        return $this->children->resolve();
    }

    /**
     * getRevision method
     *
     * @param $key
     *
     * @return \Codex\Contracts\Revisions\Revision|mixed
     */
    public function getRevision($key)
    {
        return $this->getRevisions()->get($key);
    }

    /**
     * hasRevision method
     *
     * @param $key
     *
     * @return bool
     */
    public function hasRevision($key)
    {
        return $this->getRevisions()->has($key);
    }

    public function getDefaultRevisionKey()
    {
        return $this->getRevisions()->getDefaultKey();
    }

    //region: FS Disk

    /**
     * getDefaultDiskName method.
     *
     * @return string
     */
    public function getDefaultDiskName()
    {
        return 'codex-' . $this->getKey();
    }

    /**
     * updateDisk method.
     */
    public function updateDisk()
    {
        if (null === $this->getDiskName()) {
            $this->setDiskName($this->getDefaultDiskName());
            config()->set(
                "filesystems.disks.{$this->getDiskName()}",
                $this->getDiskConfig()->toArray()
            );
        }
        $this->setFiles($this->fsm->disk($this->getDiskName()));
    }

    /**
     * setDiskName method.
     *
     * @param $diskName
     *
     * @return $this
     */
    public function setDiskName($diskName)
    {
        $this->setAttribute('disk', $diskName);

        return $this;
    }

    /**
     * getDiskName method.
     *
     * @return array|\Codex\Model\Collection|mixed
     */
    public function getDiskName()
    {
        return $this->getAttribute('disk');
    }

    /**
     * getDiskConfig method.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDiskConfig()
    {
        $default = [];
        if ($this->getDiskName() === $this->getDefaultDiskName()) {
            $default = [
                'driver' => 'codex-local',
                'root'   => $this->path,
            ];
        }

        return collect(config("filesystems.disks.{$this->getDiskName()}", $default));
    }

    /**
     * getDisk method.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getDisk()
    {
        return $this->fsm->disk($this->getDiskName());
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Factory
     */
    public function getFsm()
    {
        return $this->fsm;
    }

    /**
     * @param \Illuminate\Contracts\Filesystem\Factory $fsm
     *
     * @return Project
     */
    public function setFsm(Factory $fsm)
    {
        $this->fsm = $fsm;

        return $this;
    }

    //endregion
}
