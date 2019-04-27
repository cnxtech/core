<?php


namespace Codex\Filesystem;


use Codex\Filesystem\Adapters\LocalAdapter;
use League\Flysystem\Filesystem;

/**
 * @method LocalAdapter getAdapter()
 * @method boolean symlink($from, $to)
 * @method boolean isSymlink($path)
 * @method boolean deleteSymlink($path)
 * @method boolean hash(string $path, string $algo = 'sha1')
 * @method \Codex\Filesystem\FileCollection|\Codex\Filesystem\File[] glob(string|string[] $glob, int $flags = 0)
 * @method boolean isDir($path)
 * @method boolean isFile($path)
 */
class Local extends Filesystem
{
    public function __construct($root, $writeFlags = LOCK_EX, $linkHandling = LocalAdapter::DISALLOW_LINKS)
    {
        $adapter = new LocalAdapter($root, $writeFlags, $linkHandling);
        parent::__construct($adapter, []);
        $this->addPlugin(new Plugins\Local\Symlink());
        $this->addPlugin(new Plugins\Local\IsSymlink());
        $this->addPlugin(new Plugins\Local\DeleteSymlink());
        $this->addPlugin(new Plugins\Hash());
        $this->addPlugin(new Plugins\Glob\GlobPlugin());
        $this->addPlugin(new Plugins\IsDir());
        $this->addPlugin(new Plugins\IsFile());
        $this->addPlugin(new \League\Flysystem\Plugin\EmptyDir());
        $this->addPlugin(new \League\Flysystem\Plugin\ForcedCopy());
        $this->addPlugin(new \League\Flysystem\Plugin\ForcedRename());
        $this->addPlugin(new \League\Flysystem\Plugin\GetWithMetadata());
        $this->addPlugin(new \League\Flysystem\Plugin\ListFiles());
        $this->addPlugin(new \League\Flysystem\Plugin\ListPaths());
        $this->addPlugin(new \League\Flysystem\Plugin\ListWith());
    }

}
