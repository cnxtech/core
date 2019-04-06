<?php

namespace Codex\Addons;

class AddonFinder
{
    protected $baseDir;

    public function __construct()
    {
        $this->baseDir = base_path('codex-addons');
    }

    public function find()
    {
        return glob("{$this->baseDir}/*/*", GLOB_ONLYDIR);
    }

    /**
     * getBaseDir method
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * Set the baseDir value
     *
     * @param string $baseDir
     *
     * @return AddonFinder
     */
    public function setBaseDir($baseDir)
    {
        $this->baseDir = $baseDir;
        return $this;
    }


}
