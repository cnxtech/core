<?php


namespace Codex\Filesystem\Plugins\Local;


use League\Flysystem\Plugin\AbstractPlugin;

/**
 * Local Symlink plugin.
 *
 * Implements a symlink($symlink, $target) method for Filesystem instances using LocalAdapter.
 */
class Symlink extends AbstractPlugin
{
    /**
     * Gets the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'symlink';
    }

    /**
     * Method logic.
     *
     * Creates a symlink.
     *
     * @see http://php.net/manual/en/function.symlink.php Documentation of symlink().
     *
     * @param string $target  Symlink target.
     * @param string $symlink Symlink name.
     *
     * @return  boolean             True on success. False on failure.
     */
    public function handle($target, $symlink)
    {
        $target  = $this->filesystem->getAdapter()->applyPathPrefix($target);
        $symlink = $this->filesystem->getAdapter()->applyPathPrefix($symlink);
        return symlink($target, $symlink);
    }
}
