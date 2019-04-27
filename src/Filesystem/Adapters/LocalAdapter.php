<?php

namespace Codex\Filesystem\Adapters;

use FilesystemIterator;
use League\Flysystem\Adapter\Local as BaseLocal;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Webmozart\Glob\Glob;
use Webmozart\Glob\Iterator\GlobFilterIterator;

/**
 * This is the class Local.
 *
 * @author         Robin Radic
 * @copyright      Copyright (c) 2015, Robin Radic. All rights reserved
 */
class LocalAdapter extends BaseLocal
{
    /**
     * @param string $path
     * @param int    $mode
     *
     * @return RecursiveIteratorIterator
     */
    protected function getRecursiveDirectoryIterator($path, $mode = RecursiveIteratorIterator::SELF_FIRST)
    {
        $iterator= new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
            $mode
        );
        return $iterator;
    }

    /**
     * @param SplFileInfo $file
     *
     * @return array
     */
    protected function mapFileInfo(SplFileInfo $file)
    {
        $normalized = parent::mapFileInfo($file);

        if ('link' === $normalized[ 'type' ]) {
            $links = new \SplFileInfo($file->getRealPath());
            if($links->isDir()) {
                $normalized[ 'type' ] = 'dir';
            } elseif($links->isFile()){
                $normalized[ 'type' ] = 'file';
            }
            $normalized['links'] = $links;
        }

        return $normalized;
    }

    protected function normalizeFileInfo(SplFileInfo $file)
    {
        return $this->mapFileInfo($file);
    }

}
