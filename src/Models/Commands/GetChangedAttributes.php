<?php

namespace Codex\Models\Commands;

use Codex\Contracts\Models\ChildInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GetChangedAttributes
{
    use DispatchesJobs;

    /** @var \Codex\Contracts\Documents\Document */
    protected $model;

    /** @var bool */
    protected $withAttributes;

    /** @var array */
    protected $excludeKeys;

    /**
     * Diff constructor.
     *
     * @param \Codex\Contracts\Models\ChildInterface $model
     * @param bool                                   $withAttributes
     */
    public function __construct(ChildInterface $model, bool $withAttributes = false, array $excludeKeys = [])
    {
        $this->model          = $model;
        $this->withAttributes = $withAttributes;
        $this->excludeKeys    = $excludeKeys;
    }

    public function handle()
    {
        $diff = $this->diff();
        return $diff;
    }

    protected function diff()
    {
        $parent = [];
        $child  = [];

        $keysFilter  = function ($key) {
            if (in_array($key, $this->excludeKeys, true)) {
                return false;
            }
            $def = $this->model->getDefinition();
            if ( ! $def->hasChild($key) || $def->getChild($key)->noApi) {
                return false;
            }
            return true;
        };
        $childModel  = $this->model;
        $inheritKeys = array_filter($childModel->getInheritKeys(), $keysFilter);
        foreach ($inheritKeys as $inheritKey) {
            data_set($parent, $inheritKey, $childModel->getParent()->attr($inheritKey));
            data_set($child, $inheritKey, $childModel->attr($inheritKey));
        }
        if ($this->withAttributes) {
            $attributeKeys = array_filter($childModel->getAttributeKeys(), $keysFilter);
            foreach ($attributeKeys as $attributeKey) {
                data_set($child, $attributeKey, $childModel->attr($attributeKey));
            }
        }
        $diff= $this->diff_recursive($child, $parent);
        return $diff;
    }

    protected function diff_recursive($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if ( ! isset($array2[ $key ])) {
                    $difference[ $key ] = $value;
                } elseif ( ! is_array($array2[ $key ])) {
                    $difference[ $key ] = $value;
                } else {
                    $new_diff = $this->diff_recursive($value, $array2[ $key ]);
                    if ($new_diff != FALSE) {
                        $difference[ $key ] = $new_diff;
                    }
                }
            } elseif ( ! array_key_exists($key, $array2) || $array2[ $key ] != $value) {
                $difference[ $key ] = $value;
            }
        }
        if ( ! isset($difference)) {
            return 0;
        }
//        if (is_int(head(array_keys($difference)))) {
//            $difference = array_values($difference);
//        }
        return $difference;
    }
}
