<?php

namespace Codex\Addons;

use Illuminate\Filesystem\Filesystem;

class AddonRegistry
{
    /** @var string */
    protected $filePath;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $fs;

    /** @var array */
    protected $registry;

    /**
     * AddonRepository constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        $this->fs       = $fs;
        $this->filePath = storage_path('codex-addons.json');
    }

    protected function load($reload = false)
    {
        if ($this->registry === null || $reload) {
            if ( ! $this->fs->exists($this->filePath)) {
                $this->fs->put($this->filePath, '{}');
            }
            $this->registry = json_decode($this->fs->get($this->filePath), true);
        }
        return $this;
    }

    protected function save()
    {
        $this->fs->put($this->filePath, json_encode($this->registry, JSON_PRETTY_PRINT));
        return $this;
    }

    protected function get($name, $key, $default = null)
    {
        return data_get($this->load()->registry, "{$name}.{$key}", $default);
    }

    protected function set($name, $key, $value)
    {
        data_set($this->load()->registry, "{$name}.{$key}", $value);
        $this->save();
        return $this;
    }

    public function exists($name)
    {
        return array_key_exists($name, $this->load()->registry);
    }

    public function delete(string $name)
    {
        unset($this->load()->registry[ $name ]);
        $this->save();
        return $this;
    }

    public function create(string $name)
    {
        if ($this->exists($name)) {
            return $this;
        }
        $this->load()->registry[ $name ] = [
            'installed' => false,
            'enabled'   => false,
        ];
    }

    public function isEnabled(string $name)
    {
        return $this->get($name, 'enabled', false);
    }

    public function isInstalled(string $name)
    {
        return $this->get($name, 'installed', false);
    }

    public function setEnabled(string $name)
    {
        return $this->set($name, 'enabled', true);
    }
    public function setDisabled(string $name)
    {
        return $this->set($name, 'enabled', false);
    }

    public function setInstalled(string $name)
    {
        return $this->set($name, 'installed', true);
    }
    public function setUninstalled(string $name)
    {
        return $this->set($name, 'installed', false);
    }

}
