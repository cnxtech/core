<?php


namespace Codex\Concerns;


trait ProvidesResources
{
    public function getResources()
    {
        $resources = [
            'views'        => [],
            'translations' => [],
            'assets'       => [],
            'migrations'   => [],
            'seeds'        => [],
        ];

        foreach ($this->viewDirs as $dirName => $namespace) {
            $path                   = $this->resolvePath('viewsPath', compact('dirName'));
            $publishPath            = $this->resolvePath('viewsDestinationPath', compact('namespace'));
            $resources[ 'views' ][] = compact('namespace', 'path', 'publishPath');
        }
        foreach ($this->translationDirs as $dirName => $namespace) {
            $path                          = $this->resolvePath('translationPath', compact('dirName'));
            $publishPath                   = $this->resolvePath('translationDestinationPath', compact('namespace'));
            $resources[ 'translations' ][] = compact('namespace', 'path', 'publishPath');
        }
        foreach ($this->assetDirs as $dirName => $namespace) {
            $path                    = $this->resolvePath('assetsPath', compact('dirName'));
            $publishPath             = $this->resolvePath('assetsDestinationPath', compact('namespace'));
            $resources[ 'assets' ][] = compact('namespace', 'path', 'publishPath');
        }

        foreach ($this->migrationDirs as $dirName) {
            $path                        = $this->resolvePath('migrationsPath', compact('dirName'));
            $publishPath                 = $this->resolvePath('migrationDestinationPath');
            $resources[ 'migrations' ][] = compact('path', 'publishPath');
        }

        foreach ($this->seedDirs as $dirName) {
            $path                   = $this->resolvePath('seedsPath', compact('dirName'));
            $publishPath            = $this->resolvePath('seedsDestinationPath');
            $resources[ 'seeds' ][] = compact('path', 'publishPath');
        }

        return $resources;
    }
}
