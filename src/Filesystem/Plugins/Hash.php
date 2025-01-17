<?php


namespace Codex\Filesystem\Plugins;


use League\Flysystem\Plugin\AbstractPlugin;

class Hash extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'hash';
    }

    /**
     * Returns hash value of given path using supplied hash algorithm
     *
     * @param string $path
     * @param string $algo any algorithm supported by hash()
     *
     * @return string|bool
     * @see http://php.net/hash
     */
    public function handle($path, $algo = 'sha256')
    {
        if ( ! in_array($algo, hash_algos())) {
            throw new \InvalidArgumentException('Hash algorithm ' . $algo . ' is not supported');
        }
        $stream = $this->filesystem->readStream($path);
        if ($stream === false) {
            return false;
        }
        $hc = hash_init($algo);
        hash_update_stream($hc, $stream);
        return hash_final($hc);
    }
}
