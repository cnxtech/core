<?php

namespace Codex\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * The FilesTrait provides get/set methods for a Filesystem instance.
 *
 * @package Codex\Concerns
 * @author  Robin Radic
 *
 */
trait HasFiles
{
    private $files;

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the filesystem instance.
     *
     * @param mixed|string|\Illuminate\Contracts\Filesystem\Filesystem $files The filesystem instance
     *
     * @return $this
     */
    public function setFiles(Filesystem $files)
    {
        $this->files = $files;

        return $this;
    }
}
