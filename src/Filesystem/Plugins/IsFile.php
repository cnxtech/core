<?php


namespace Codex\Filesystem\Plugins;


use League\Flysystem\Plugin\AbstractPlugin;

class IsFile extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'IsFile';
    }

    public function handle($path)
    {
        if ( ! $this->filesystem->has($path)) {
            return false;
        }
        return data_get($this->filesystem->getMimetype($path), 'type') === 'file';
    }
}
