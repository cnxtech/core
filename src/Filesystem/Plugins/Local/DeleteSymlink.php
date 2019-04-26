<?php


namespace Codex\Filesystem\Plugins\Local;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\Plugin\AbstractPlugin;
use League\Flysystem\PluginInterface;

/**
 * Local DeleteSymlink plugin.
 *
 * Implements a deleteSymlink($symlink) method for Filesystem instances using LocalAdapter.
 */
class DeleteSymlink extends AbstractPlugin
{
    /**
     * Gets the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'deleteSymlink';
    }

    /**
     * Method logic.
     *
     * Deletes a symlink.
     *
     * @see http://php.net/manual/en/function.unlink.php Documentation of unlink().
     *
     * @param string $symlink Symlink name.
     *
     * @return  boolean             True on success. False on failure.
     */
    public function handle($symlink)
    {
        $symlink = $this->filesystem->getAdapter()->applyPathPrefix($symlink);
        if ( ! is_link($symlink)) {
            return false;
        }
        return unlink($symlink);
    }
}
