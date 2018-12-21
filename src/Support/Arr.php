<?php

namespace Codex\Support;

/**
 * This is the class Arr.
 *
 * @package Codex\Support
 * @author  Robin Radic
 * @mixin \Illuminate\Support\Arr
 */
class Arr
{
    public function explodeToPaths()
    {
        return function ($paths) {
            $explodePath = function ($path) {
                $matches = [];
                $matched = preg_match('/(.*?)\.{(.*?)}/', $path, $matches);
                if ($matched !== 1) {
                    return explode(',', $path);
                }
                $paths = collect(explode(',', $matches[ 2 ]))
                    ->map(function ($key) use ($matches) {
                        return $matches[ 1 ] . '.' . $key;
                    });
                return $paths->toArray();
            };

            $paths = \is_string($paths) ? [ $paths ] : $paths;
            return array_map(function ($path) use ($explodePath) {
                return $explodePath($path);
            }, $paths);
        };
    }


    public function merge()
    {
        $merge = function (array $arr1, array $arr2, $unique = true) use (&$merge) {
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
                        $value = $merge($arr1[ $key ], $value, $unique);
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
        };
        return $merge;
    }
}
