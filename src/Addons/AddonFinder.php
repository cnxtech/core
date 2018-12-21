<?php

namespace Codex\Addons;

class AddonFinder
{
    protected $baseDir;

    /**
     * AddonFinder constructor.
     */
    public function __construct()
    {
        $this->baseDir = base_path('codex-addons');
    }

    public function find()
    {
        return glob("{$this->baseDir}/*/*", GLOB_ONLYDIR);
//        $paths = [];
//        foreach (glob("{$this->baseDir}/*/*", GLOB_ONLYDIR) as $path) {
//            $name    = trim(str_replace_first($this->baseDir, '', $path), '/');
//            $paths[] = compact('name', 'path');
//        }
//        return $paths;
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
