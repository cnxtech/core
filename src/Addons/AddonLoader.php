<?php

namespace Codex\Addons;

class AddonLoader
{
    /**
     * load method
     *
     * @param $path
     *
     * @return $this
     * @throws \Exception
     */
    public function load($path)
    {
        if ( ! file_exists($path . '/composer.json')) {
            throw new \Exception("Composer file not found at {$path}/composer.json");
        }

        if ( ! $composer = json_decode(file_get_contents($path . '/composer.json'), true)) {
            throw new \Exception("A JSON syntax error was encountered in {$path}/composer.json");
        }

        $autoload = array_merge(data_get($composer, 'autoload.psr-0', []), data_get($composer, 'autoload.psr-4', []));

        foreach($autoload as $namespace => $directory){
            $segment = last(explode('\\', rtrim($namespace, '\\')));
            $provider = $namespace  . $segment . 'ServiceProvider';
            $addon =  $namespace . $segment . 'Addon';
            $hasProvider = class_exists($provider);
            $hasAddon =  class_exists($addon);
            if(class_exists($addon)){

            }
        }

        return $composer;
//        if (!array_key_exists('autoload', $composer)) {
//
//        }

//        foreach (array_get($composer['autoload'], 'psr-4', []) as $namespace => $autoload) {
//            $this->loader->addPsr4($namespace, $path . '/' . $autoload, false);
//        }
//
//        foreach (array_get($composer['autoload'], 'psr-0', []) as $namespace => $autoload) {
//            $this->loader->add($namespace, $path . '/' . $autoload, false);
//        }

    }
}
