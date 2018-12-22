<?php

namespace Codex\Attributes\Commands;

use Codex\Attributes\AttributeConfigBuilderGenerator;
use Codex\Contracts\Mergable\Mergable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use Symfony\Component\Config\Definition\Processor;


class MergeAttributes
{
    protected $target;

    public function __construct(Mergable $target)
    {
        $this->target = $target;
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
        $defaultAttributes = $this->target->getDefaultAttributes();
        $parentAttributes  = $this->target->getParentAttributes();
        $attributes        = $this->target->getAttributes();

        if ($defaultAttributes instanceof Repository) {
            $defaultAttributes = $defaultAttributes->all();
        }
        $inherited = $this->getInheritedParentAttributes();
        $result    = Arr::merge($inherited, $defaultAttributes);
        $result    = Arr::merge($result, $attributes);

        foreach ($this->getMergeKeys() as $mergeKey) {
            $result = Arr::merge($result, data_get($parentAttributes, $mergeKey, []));
        }


        $builder   = $generator->generateGroup($this->target->getAttributeDefinitions()->name);
        $processor = new Processor();
        $final     = $processor->process($builder->buildTree(), [
            [
                'layout' => [
                    'header' => [
                        'menu' => [
                            [
                                'label'    => 'Item',
                                'path'     => '/',
                                'children' => [
                                    [
                                        'label'    => 'ItemChild',
                                        'path'     => '/child',
                                        'children' => [
                                            [
                                                'label' => 'ItemChildChild',
                                                'path'  => '/child/child',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->target->setMergedAttributes($final);

        $parent = array_dot($parentAttributes);
//        $target  = array_dot(array_only($result, $this->getInheritKeys()));
        $target  = array_dot(array_only($final, $this->getInheritKeys()));
        $changes = [];

        foreach ($target as $key => $val) {
            if ( ! array_key_exists($key, $parent) || $parent[ $key ] !== $val) {
                $changes[ $key ] = $val;
            }
        }


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
