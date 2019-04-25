<?php

namespace Codex\Models\Concerns;

use Codex\Contracts\Models\MergableDataProviderInterface;
use Codex\Models\MergeDataProvider;


/**
 * This is the HasMergableAttributes trait.
 *
 * @author  Robin Radic
 * @property array $mergePaths;
 */
trait HasMergableAttributes
{

    /**
     * @var \Codex\Contracts\Models\MergableDataProviderInterface
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

    public function getParentAttributes()
    {
        return $this->getParent()->getAttributes();
    }
}
