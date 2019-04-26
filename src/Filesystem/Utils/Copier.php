<?php


namespace Codex\Filesystem\Utils;


use Codex\Filesystem\File;
use Codex\Filesystem\FileCollection;
use Codex\Filesystem\Local;
use Illuminate\Support\Arr;
use League\Flysystem\FilesystemInterface;
use Webmozart\Glob\Glob;

class Copier
{
    /** @var \League\Flysystem\FilesystemInterface|\Codex\Filesystem\Local */
    protected $from;

    /** @var \League\Flysystem\FilesystemInterface|\Codex\Filesystem\Local */
    protected $to;

    /**
     * LocalCopier constructor.
     *
     * @param \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface|string $from
     * @param \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface|string $to
     */
    public function __construct($from, $to)
    {
        $this->from = $from instanceof FilesystemInterface ? $from : new Local((string)$from);
        $this->to   = $to instanceof FilesystemInterface ? $to : new Local((string)$to);
    }

    /**
     * @param string          $src
     * @param string|string[] $dest
     * @param array           $options
     */
    public function copy($src, $dest, array $options = [])
    {
        $srcItems  = $this->getSourceItems($src, $dest, $options);
        $destItems = $this->getDestItems($src, $srcItems, $dest, $options);
        foreach ($destItems as $destItem) {
            $srcItem = $destItem->getSrcFile();
            if ($srcItem !== null) {
                $key  = $destItem->key();
                $path = path_njoin(data_get($options, 'path'), $key);
                $this->to->put($path, $srcItem->read());
                $this->copied[] = [
                    'src'      => $src,
                    'dest'     => $dest,
                    'destItem' => $destItem,
                    'srcItem'  => $srcItem,
                ];
            }
        }
    }

    /**
     * @param string $src
     *
     * @return \Codex\Filesystem\FileCollection|\Codex\Filesystem\File[]
     */
    protected function getSourceItems($src, $dest, array $options = [])
    {
        $srcItems = $this->from->glob($src);

        if ($filter = data_get($options, 'filter', data_get($options, 'filters'))) {
            $srcItems = $this->applyFilter($srcItems, $filter);
        }

        foreach ($srcItems as $path => $item) {
            $this->expandSourceItem($item, $srcItems);
        }

        return $srcItems;
    }

    protected function expandSourceItem(File $item, FileCollection $items)
    {
        if ( ! $item->isDir()) {
            return $items;
        }
        $items->forget($item->key());
        $this->from
            ->glob([
                path_njoin($item->getAbsolutePath(), '**/*'),
                path_njoin($item->getAbsolutePath(), '**/*.*'),
            ])
            ->files()
            ->filter(function (File $file) use ($items) {
                return ! $items->has($file->key());
            })
            ->each(function (File $file) use ($items) {
                $items->put($file->key(), $file);
            });
        return $items;
    }

    protected function applyFilter(FileCollection $items, $filter)
    {
        if (is_array($filter)) {
            foreach ($filter as $_filter) {
                $items = $this->applyFilter($items, $_filter);
            }
            return $items;
        }
        if (is_string($filter)) {
            return $items->filter->{$filter}();
        }
        return $items->filter($filter);
    }


    /**
     * @param                                  $src
     * @param \Codex\Filesystem\FileCollection $srcItems
     * @param                                  $dest
     * @param array                            $options
     *
     * @return \Codex\Filesystem\FileCollection|\Codex\Filesystem\File[]
     */
    protected function getDestItems($src, FileCollection $srcItems, $dest, array $options = [])
    {
        $srcPrefix = Glob::getStaticPrefix($src);

        $dest  = Arr::wrap($dest);
        $items = FileCollection::make();
        foreach ($srcItems as $srcPath => $srcItem) {
            $relativeSrcPath = path_relative($srcPath, $srcPrefix);
            foreach ($dest as $path) {
                $path = $path === '' ? '/' : $path;
                if (ends_with($path, '/')) {
                    $path = path_njoin($path, $relativeSrcPath);
                }
                $file = new File($this->to, $path, $srcItem);
                $items->put($path, $file);
            }
        }
        return $items;
    }

    protected $copied = [];

    public function getCopied()
    {
        return $this->copied;
    }

    /**
     * @return \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface $from
     *
     * @return Copier
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param \Codex\Filesystem\Local|\League\Flysystem\FilesystemInterface $to
     *
     * @return Copier
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }


}
