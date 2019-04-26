<?php


namespace Codex\Filesystem\Adapters;

use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem as Flysystem;

class TmpAdapter extends LocalAdapter
{

    /**
     * Creates a temporary directory
     *
     * @param string $prefix
     * @param null   $dir
     */
    public function __construct($prefix = '', $dir = null, $writeFlags = LOCK_EX, $linkHandling = self::DISALLOW_LINKS, array $permissions = [])
    {
        $maxTries = 1024;
        if (empty($dir)) {
            $dir = sys_get_temp_dir();
        } else {
            $dir = rtrim($dir, $this->pathSeparator);
        }
        while ($maxTries > 0) {
            $path = $dir . $this->pathSeparator . md5(uniqid($prefix, true));
            if ( ! file_exists($path) && mkdir($path, 0755, true) && is_dir($path) ) {
                break;
            }
            $maxTries--;
        }

        if ($maxTries === 0) {
            throw new \RuntimeException("Couldn't create temporary directory, giving up");
        }

        parent::__construct($path, $writeFlags, $linkHandling, $permissions);
    }

    /**
     * Removes temporary directory
     *
     * @throws \League\Flysystem\FileExistsException
     */
    public function __destruct()
    {
        $fs = new Flysystem(new LocalAdapter(dirname($this->getPathPrefix())));
        $fs->deleteDir(basename($this->getPathPrefix()));
        $fs->assertAbsent($this->getPathPrefix());
    }
}
