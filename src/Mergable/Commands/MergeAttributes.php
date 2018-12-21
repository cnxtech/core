<?php

namespace Codex\Mergable\Commands;

use Codex\Contracts\Mergable\Mergable;

/**
 * This is the class MergeAttributes.
 *
 * @package Codex\Projects\Commands
 * @author  Robin Radic
 */
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
     * @return void
     */
    public function handle()
    {
        $inheritableKeys   = $this->target->getInheritableKeys();
        $defaultAttributes = $this->target->getDefaultAttributes();
        $parentAttributes  = $this->target->getParentAttributes();
        $parentAttributes  = array_only($parentAttributes, $inheritableKeys);
        $attributes        = $this->target->getAttributes();

        $result = $parentAttributes;
        $result = static::merge($result, $defaultAttributes);
        $result = static::merge($result, $attributes);
        $this->target->setMergedAttributes($result);

        $parent = array_dot($parentAttributes);
        $target = array_dot(array_only($result, $inheritableKeys));
        $changes = [];

        foreach($target as $key => $val){
            if(!array_key_exists($key, $parent) || $parent[$key] !== $val){
                $changes[$key] = $val;
            }
        }


        $a = 'a';
    }

    /**
     * onlyDot method
     *
     * @param array $array
     * @param       $keys
     *
     * @return array
     */
    protected function onlyDot(array $array, $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            data_set($result, $key, data_get($array, $key));
        }

        return $result;
    }


    /**
     * merge method
     * unique = false === append (double values in arrays may occur)
     * unique = true === merge
     *
     * @param array $arr1
     * @param array $arr2
     * @param bool  $unique
     *
     * @return array
     */
    public static function merge(array $arr1, array $arr2, $unique = true)
    {
        if (empty($arr1)) {
            return $arr2;
        }

        if (empty($arr2)) {
            return $arr1;
        }

        foreach ($arr2 as $key => $value) {
            if (\is_int($key)) {
                if ( ! $unique || ! \in_array($value, $arr1, true)) {
                    $arr1[] = $value;
                }
            } elseif (\is_array($arr2[ $key ])) {
                if ( ! isset($arr1[ $key ])) {
                    $arr1[ $key ] = [];
                }
                if (\is_array($arr1[ $key ])) {
                    $value = static::merge($arr1[ $key ], $value, $unique);
                }

                if (\is_int($key)) {
                    $arr1[] = $unique ? array_unique($value) : $value;
                } else {
                    $arr1[ $key ] = $value;
                }
            } else {
                $arr1[ $key ] = $value;
            }
        }
        return $arr1;
    }

}
