<?php


namespace Codex\Tests\Fixture;


use Codex\Support\DotArrayWrapper;

trait CreatesConfigData
{
    protected $configFileData = [];

    protected $configFileNames = [
        'codex',
        'codex.layout',
        'codex.processors',
        'projects',
        'revisions',
    ];

    protected function requireConfig($name)
    {
        if ( ! array_key_exists($name, $this->configFileData)) {
            data_set($this->configFileData, $name, require __DIR__ . "/config/{$name}.php");
        }
        return data_get($this->configFileData, $name, []);
    }

    protected function requireWrappedConfig($name)
    {
        return DotArrayWrapper::make($this->requireConfig($name));
    }

    protected function createConfig()
    {
        $config = [];
        foreach($this->configFileNames as $name){
            data_set($config, $name, $this->requireConfig($name));
        }
        return $config;
    }

    /**
     * createWrappedConfig method
     *
     * @return \Codex\Support\DotArrayWrapper
     */
    protected function createWrappedConfig()
    {
        return DotArrayWrapper::make($this->createConfig());
    }
}
