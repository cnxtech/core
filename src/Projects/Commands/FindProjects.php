<?php

namespace Codex\Projects\Commands;

use Codex\Hooks;
use Symfony\Component\Finder\Finder;

/**
 * This is the class FindProjectConfigs.
 *
 * @package Codex\Projects\Commands
 * @author  Robin Radic
 */
class FindProjects
{

    /**
     * @var null|string
     */
    protected $docsDir;

    /**
     * FindProjectConfigs constructor.
     *
     * @param string $docsDir
     */
    public function __construct($docsDir = null)
    {
        $this->docsDir = $docsDir ?? codex()->paths[ 'docs' ];
    }

    /**
     * handle method
     *
     * @return array
     */
    public function handle()
    {
        $files = Finder::create()
            ->in($this->docsDir)
            ->depth(1)
            ->name('config.php')
            ->files();

        $paths = [];
        foreach (iterator_to_array($files->getIterator()) as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $key           = basename(\dirname($file->getRealPath()));
            $paths[ $key ] = $file->getRealPath();
        }

        $paths = Hooks::waterfall('projects.found', $paths, [ $this ]);
        return $paths;
    }

}
