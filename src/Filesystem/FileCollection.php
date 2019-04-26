<?php


namespace Codex\Filesystem;


use Illuminate\Support\Collection;

/**
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $average
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $avg
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $contains
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $each
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $every
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $filter
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $first
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $flatMap
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $groupBy
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $keyBy
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $map
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $max
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $min
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $partition
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $reject
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $sortBy
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $sortByDesc
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $sum
 * @property-read \Illuminate\Support\HigherOrderCollectionProxy|\Codex\Filesystem\File $unique
 * @method \Codex\Filesystem\File get($key, $default = null)
 */
class FileCollection extends Collection
{
    /** @var \Codex\Filesystem\File[] */
    protected $items;

    /** @return $this */
    public function directories()
    {
        return $this->filter->isDir();
    }

    /** @return $this */
    public function files()
    {
        return $this->filter->isFile();
    }

    /** @return $this */
    public function withoutFilesExistingInDirs()
    {
        $directories = $this->directories();
        return $this->filter(function (File $item) use ($directories) {
            if ( ! $item->isFile()) {
                return true;
            }
            return $this->hasParentDirectory($item, $directories) === false;
        });
    }

    /** @return $this */
    public function withoutDirsExistingInDirs()
    {
        $directories = $this->directories();
        return $this->filter(function (File $item) use ($directories) {
            if ( ! $item->isDir()) {
                return true;
            }
            return $this->hasParentDirectory($item, $directories) === false;
        });
    }

    /** @return $this */
    public function withoutDirectoryChildren()
    {
        return $this
            ->withoutDirsExistingInDirs()
            ->withoutFilesExistingInDirs();
    }


    /**
     * @param \Codex\Filesystem\File $info
     * @param ?$this $collection
     *
     * @return bool
     */
    public function hasParentDirectory(File $info, $collection = null)
    {
        $collection = $collection ?: $this;
        foreach ($collection->items as $item) {
            if ($item === $info) {
                continue;
            }
            if ($item->isDir() && starts_with($info->getPath(), $item->getPath())) {
                return true;
            }
        }
        return false;
    }

}
