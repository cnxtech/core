<?php

namespace Codex\Mergable\Concerns;

use Codex\Contracts\Mergable\MergableDataProviderInterface;
use Codex\Mergable\MergeDataProvider;


/**
 * This is the HasMergableAttributes trait.
 *
 * @author  Robin Radic
 * @property array $mergePaths;
 */
trait HasMergableAttributes
{
    protected $changed = [];

    /**
     * @var \Codex\Contracts\Mergable\MergableDataProviderInterface
     */
    protected $mergableDataProvider;

    public function initializeHasMergableAttributes()
    {
        if ($this->mergableDataProvider === null) {
            $this->setMergableDataProvider(MergeDataProvider::make(config()));
        }
    }

    public function setMergableDataProvider(MergableDataProviderInterface $mergableDataProvider)
    {
        $this->mergableDataProvider = $mergableDataProvider;
        return $this;
    }

    public function getDefaultAttributes()
    {
        return $this->mergableDataProvider->get($this::DEFAULTS_PATH);
    }

    public function setMergedAttributes(array $attributes)
    {
        $this->setRawAttributes($attributes);
        $this->addHidden($hidden = array_keys(array_except($attributes, array_merge($this->getVisible(), [ $this->getKeyName() ]))));
        return $this;
    }

    public function setChanged(array $keys = [])
    {
        $this->changed = $keys;
        return $this;
    }
    public function getChanged()
    {
        return $this->changed;
    }

    public function getParentAttributes()
    {
        return $this->getParent()->getAttributes();
    }
}
