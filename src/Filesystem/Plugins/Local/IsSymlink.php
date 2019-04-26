<?php


namespace Codex\Filesystem\Plugins\Local;


use League\Flysystem\Plugin\AbstractPlugin;

/**
 * Local IsSymlink plugin.
 *
 * Implements a isSymlink($filename) method for Filesystem instances using LocalAdapter.
 */
class IsSymlink extends AbstractPlugin
{

    /**
     * Gets the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'isSymlink';
    }

    /**
     * Method logic.
     *
     * Tells whether the specified $filename exists and is a symlink.
     *
     * @see http://php.net/manual/en/function.is-link.php Documentation of is_link().
     *
     * @param string $filename Filename.
     *
     * @return  boolean             True if $filename is a symlink. Else false.
     */
    public function handle($filename)
    {
        $filename = $this->filesystem->getAdapter()->applyPathPrefix($filename);
        return is_link($filename);
    }
}
