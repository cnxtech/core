<?php


namespace Codex\Filesystem\Plugins\Glob;


use Codex\Filesystem\File;
use Codex\Filesystem\FileCollection;
use Illuminate\Support\Arr;
use League\Flysystem\Plugin\AbstractPlugin;
use Webmozart\Glob\Iterator\GlobFilterIterator;

/**
 * @property \Codex\Filesystem\Local $filesystem
 */
class GlobPlugin extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'glob';
    }

    public function handle($globs, $flags = 0)
    {
        $fs      = $this->filesystem;
        $adapter = $this->filesystem->getAdapter();
        $items   = new FileCollection();
        $globs   = Arr::wrap($globs);
        foreach ($globs as $glob) {
            $paths = $this->glob($glob);
            foreach ($paths as $key => $value) {
                $items->put($key, $value);
            }
        }
        return $items;
    }

    /** @var \Illuminate\Support\Collection|array[] */
    protected $paths;

    protected function getPaths()
    {
        if ($this->paths === null) {
            $paths       = $this->filesystem->listContents('', true);
            $this->paths = collect($paths)->mapWithKeys(function ($data) {
                $item = new File($this->filesystem, $data['path']);
                return [ $item->key() => $item ];
            });
        }
        return $this->paths;
    }

    public function getGlobIterator($glob)
    {
        $glob          = path_absolute($glob, '/');
        $paths         = $this->getPaths()->toArray();
        $innerIterator = new \ArrayIterator($paths);
        $iterator      = new GlobFilterIterator(path_absolute($glob, '/'), $innerIterator, GlobFilterIterator::FILTER_KEY | GlobFilterIterator::KEY_AS_KEY);

        return $iterator;
    }

    public function glob($glob)
    {
        $iterator = $this->getGlobIterator($glob);
        $results  = iterator_to_array($iterator, true);
        return $results;
    }
}
