<?php


namespace Codex\Filesystem\Utils;


use Codex\Filesystem\Local;
use Codex\Filesystem\Plugins\Glob\GlobPlugin;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Plugin\EmptyDir;

class Cleaner
{
    /** @var \League\Flysystem\FilesystemInterface|\Codex\Filesystem\Local */
    protected $fs;

    /**
     * LocalCopier constructor.
     *
     * @param \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface|string $fs
     */
    public function __construct($fs)
    {
        $this->fs = $fs instanceof FilesystemInterface ? $fs : new Local((string)$fs);
        $this->fs->addPlugin(new GlobPlugin());
        $this->fs->addPlugin(new EmptyDir());
    }

    public function clean($glob, array $options = [])
    {
        foreach ($this->fs->glob($glob) as $item) {
            $path = $item->key();
            if ($item->isDir()) {
                $this->fs->emptyDir($path);
                $this->fs->deleteDir($path);
            } elseif ($item->isFile()) {
                $this->fs->delete($path);
            }
        }
    }
}
