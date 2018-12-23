<?php

namespace Codex\Mergable\Commands;

use Codex\Attributes\AttributeConfigBuilderGenerator;
use Codex\Contracts\Mergable\Mergable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use Symfony\Component\Config\Definition\Processor;


class MergeAttributes
{
    protected $target;

    /** @var array */
    protected $attributes;

    public function __construct(Mergable $target, array $attributes = [])
    {
        $this->target     = $target;
        $this->attributes = $attributes;
    }

    /**
     * handle method
     *
     * @param \Codex\Attributes\AttributeConfigBuilderGenerator $generator
     *
     * @return void
     */
    public function handle(AttributeConfigBuilderGenerator $generator)
    {
        // 1 : gather all the arrays needed
        $defaultAttributes = $this->target->getDefaultAttributes();
        $parentAttributes  = $this->target->getParentAttributes();
        $attributes        = $this->target->getAttributes();
        if ($defaultAttributes instanceof Repository) {
            $defaultAttributes = $defaultAttributes->all();
        }
        $inherited = $this->getInheritedParentAttributes();


        // 2 : merge all the gathered arrays
        $result = Arr::merge($inherited, $defaultAttributes);
        $result = Arr::merge($result, $attributes);
        foreach ($this->getMergeKeys() as $mergeKey) {
            $result = Arr::merge($result, data_get($parentAttributes, $mergeKey, []));
        }
        $result = Arr::merge($result, $this->attributes);


        // 3 : Generate and build the Config tree from attribute definitions
        $builder   = $generator->generateGroup($this->target->getAttributeDefinitions()->name);
        $processor = new Processor();
        // 4 : Filter the merged result to only include nodes defined/accepted with the target
        $final = $processor->process($builder->buildTree(), [ $result ]);
        // 5 : Set the final result data on the target
        $this->target->setMergedAttributes($final);

        // 6 : Resolve what changes there have been to the data compared to the parent and let the kid know
        $finalInherited = array_only($final, $this->getInheritKeys());
        $parent         = array_dot($parentAttributes);
        $target         = array_dot($finalInherited);
        $changed        = [];
        foreach ($target as $key => $val) {
            if ( ! array_key_exists($key, $parent) || $parent[ $key ] !== $val) {
                $k = head(preg_split('/\.\d/', $key));
                if ( ! \in_array($k, $changed, true)) {
                    $changed[] = $k;
                }
            }
        }
        $this->target->setChanged($changed);
        $a = 'a';
    }

    protected function getInheritKeys()
    {
        $keys     = $this->target->getAttributeDefinitions()->inheritKeys;
        $resolved = [];
        foreach ($keys as $sourceKey => $targetKey) {
            if (is_int($sourceKey)) {
                $sourceKey = $targetKey;
            }
            $resolved[ $sourceKey ] = $targetKey;
        }
        return $resolved;
    }

    protected function getMergeKeys()
    {
        return $this->target->getAttributeDefinitions()->mergeKeys;
    }

    protected function getInheritedParentAttributes()
    {
        $parentAttributes = $this->target->getParentAttributes();
        $attributes       = [];
        foreach ($this->getInheritKeys() as $sourceKey => $targetKey) {
            data_set($attributes, $targetKey, data_get($parentAttributes, $sourceKey));
        }
        return $attributes;
    }

}
