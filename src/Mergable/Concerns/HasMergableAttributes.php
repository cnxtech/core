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

    public function getChanges()
    {
        $changes = [];
        $changed = $this->getChanged();
        foreach ($changed as $attr) {
            $definitions = $this->getAttributeDefinitions();
            $segments    = explode('.', $attr);
            $segment     = head($segments);
            if ( ! $definitions->hasChild($segment)) {
                continue;
            }
            $definition = $definitions->getChild($segment);
            if ($definition->noApi === true) {
                continue;
            }
            data_set($changes, $attr, $this->attr($attr));
        }
//            foreach ($segments as $i => $segment) {
//                if(!$definitions->hasChild($segment)){
//                    break;
//                }
//                $definition = $definitions->getChild($segment);
//                $key        = implode('.', array_slice($segments, 0, $i +1));
//                if ($definition->noApi === true) {
//                    break;
//                }
//                data_set($changes, $key, $this->attr($key));
//                $definitions = $definition;
//            }
        return $changes;
    }
}
