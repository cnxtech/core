<?php

namespace Codex\Projects;

use Codex\Concerns;
use Codex\Contracts\Models\ChildInterface;
use Codex\Contracts\Models\ParentInterface;
use Codex\Contracts\Projects\Project as ProjectContract;
use Codex\Hooks;
use Codex\Models\Concerns\HasChildren;
use Codex\Models\Concerns\HasParent;
use Codex\Models\Model;
use Codex\Revisions\RevisionCollection;
use Codex\Support\DB;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\Filesystem;

/**
 * This is the class Project.
 *
 * @package Codex\Projects
 * @author  Robin Radic
 * @method \Codex\Codex getParent()
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

    /** @var \Codex\Codex */
    protected $parent;

    /** @var \Codex\Revisions\RevisionCollection */
    protected $children;

    /** @var \Illuminate\Contracts\Filesystem\Factory */
    protected $fsm;

    /** @var string */
    protected $configFilePath;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $fs;


    /**
     * Project constructor.
     *
     * @param array                                    $attributes
     * @param \Codex\Revisions\RevisionCollection      $revisions
     * @param \Illuminate\Contracts\Filesystem\Factory $fsm
     */
    public function __construct(array $attributes, RevisionCollection $revisions, Factory $fsm, Filesystem $fs)
    {
        DB::startMeasure('project:'. $attributes['key']);
        $this->fsm = $fsm;
        $this->fs  = $fs;

        $this->setParent($this->getCodex());
        $this->setChildren($revisions->setParent($this));
        $registry   = $this->getCodex()->getRegistry()->resolve('projects');
        $attributes = Hooks::waterfall('project.initialize', $attributes, [ $registry, $this ]);
        $this->initialize($attributes, $registry);
        $this->addGetMutator('inherits', 'getInheritKeys', true, true);
        $this->addGetMutator('changes', 'getChanges', true, true);
        Hooks::run('project.initialized', [ $this ]);

    }

    public function url($revisionKey = null, $documentKey = null, $absolute = true)
    {
        return $this->getCodex()->url($this->getKey(), $revisionKey, $documentKey, $absolute);
    }

    /** @return \Codex\Contracts\Revisions\Revision[]|\Codex\Models\EloquentCollection */
    public function revisions()
    {
        return $this->getRevisions()->toRelationship();
    }

    /** @return \Codex\Contracts\Revisions\Revision[]|\Codex\Revisions\RevisionCollection */
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
        return $this->fs->lastModified($this->configFilePath);
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
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
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
