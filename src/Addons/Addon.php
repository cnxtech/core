<?php

namespace Codex\Addons;

use Codex\Concerns\HasCallbacks;

class Addon
{
    use HasCallbacks;


    /** @var bool */
    protected $installed = false;

    /** @var bool */
    protected $enabled = false;

    /** @var string */
    protected $vendor = null;

    /** @var string */
    protected $slug = null;

    /** @var string */
    protected $path = null;

    /** @var array|string[] */
    protected $extensions;

    public function getName()
    {
        return $this->vendor . '/' . $this->slug;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the enabled value
     *
     * @param bool $enabled
     *
     * @return Addon
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path value
     *
     * @param null $path
     *
     * @return Addon
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function path(...$parts)
    {
        array_unshift($parts, $this->getPath());
        return path_join($parts);
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Set the vendor value
     *
     * @param string $vendor
     *
     * @return Addon
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the slug value
     *
     * @param string $slug
     *
     * @return Addon
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return $this->installed;
    }

    /**
     * Set the installed value
     *
     * @param bool $installed
     *
     * @return Addon
     */
    public function setInstalled($installed)
    {
        $this->installed = $installed;
        return $this;
    }

    /**
     * getServiceProvider method
     *
     * @return string
     */
    public function getServiceProvider()
    {
        return get_class($this) . 'ServiceProvider';
    }

    /**
     * newServiceProvider method
     *
     * @return \Codex\Addons\AddonServiceProvider
     */
    public function newServiceProvider()
    {
        return app()->make(
            $this->getServiceProvider(),
            [
                'container' => app(),
                'app'       => app(),
                'addon'     => $this,
            ]
        );
    }

    /**
     * @return array|string[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Set the extensions value
     *
     * @param array|string[] $extensions
     *
     * @return Addon
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }


    public function __toString()
    {
        return $this->getName();
    }


}
