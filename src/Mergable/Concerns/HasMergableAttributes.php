<?php

namespace Codex\Mergable\Concerns;

use Codex\Contracts\Mergable\Mergable;
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

    public function initializeHasMergableAttributes()
    {
        if ($this->mergableDataProvider === null) {
            $this->setMergableDataProvider(MergeDataProvider::make(config()));
        }
    }

    /**
     * @var \Codex\Contracts\Mergable\MergableDataProviderInterface
     */
    protected $mergableDataProvider;

    public function setMergableDataProvider(MergableDataProviderInterface $mergableDataProvider)
    {
        $this->mergableDataProvider = $mergableDataProvider;
        return $this;
    }

    public function getMergableAttributesCasts()
    {
        return $this->mergableDataProvider->get($this->mergePaths[ Mergable::CASTS_PATH ]);
    }

    public function getDefaultAttributes()
    {
        return $this->mergableDataProvider->get($this::DEFAULTS_PATH);
    }

    public function getInheritableKeys()
    {
        return $this->mergableDataProvider->get($this->mergePaths[ Mergable::INHERITS_PATH ]);
    }

    public function setMergedAttributes(array $attributes)
    {
        $this->setRawAttributes($attributes);
//        $this->addVisible($this->getInheritableKeys());
        $this->addHidden($hidden = array_keys(array_except($attributes, array_merge($this->getVisible(), [ $this->getKeyName() ]))));
        return $this;
    }

    /**
     * getParentAttributes method
     *
     * @return array
     */
    public function getParentAttributes()
    {
        return $this->getParent()->getAttributes();
    }
}
