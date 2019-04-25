<?php

namespace Codex\Attributes;

use Codex\Exceptions\NotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

/**
 * @method $this default($value)
 * @method $this parent(AttributeDefinition $value)
 * @method $this name($value)
 * @method $this noApi(bool $value = true)
 * @method $this required(bool $value = true)
 * @property string                                                     $name
 * @property AttributeType                                              $type
 * @property ApiDefinition                                              $api
 * @property \Illuminate\Support\Collection|array|AttributeDefinition[] $children
 * @property AttributeDefinition|null                                   $parent
 * @property boolean                                                    $noApi
 * @property mixed                                                      $default
 * @property boolean                                                    $required
 * @property array                                                      $inheritKeys
 * @property array                                                      $mergeKeys
 *
 * @method static AttributeType STRING()
 * @method static AttributeType BOOL()
 * @method static AttributeType INT()
 * @method static AttributeType FLOAT()
 * @method static AttributeType ARRAY()
 * @method static AttributeType MIXED()
 * @method static AttributeType RECURSIVE()
 * @method static AttributeType RECURSE()
 * @method static AttributeType MAP()
 *
 */
class AttributeDefinition extends Fluent
{

    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, AttributeType::getNames(), true)) {
            return forward_static_call_array([ AttributeType::class, $name ], $arguments);
        }
    }

    public function __construct($parent = null, array $attributes = [])
    {
        $children = new Collection();
        parent::__construct(array_merge(compact('parent', 'children'), $attributes));
        $this
            ->parent($parent)
            ->name('root')
            ->type('array')
            ->api('Mixed')
            ->inheritKeys([])
            ->mergeKeys([]);
    }

    public function clone()
    {
        $clone           = new static($this->parent, $this->attributes);
        $clone->children = $this->children->map(function (AttributeDefinition $child) {
            return $child->clone();
        });
        return $clone;
    }

    /** @param AttributeType|string $value */
    public function type($value)
    {
        if ( ! $value instanceof AttributeType) {
            $value = AttributeType::get($value);
        }
        if ( ! $this->api) {
            $this->api($value->toApiType());
        }
        $this->attributes[ 'type' ] = $value;
        return $this;
    }

    public function end()
    {
        return $this->parent ?? $this;
    }

    /**
     * api method
     *
     * @param string|array|\Codex\Attributes\ApiDefinition $type
     * @param array|null                                   $options
     *
     * @return $this
     */
    public function api($type, array $options = [])
    {
        if ( ! $type instanceof ApiDefinition) {
            if (is_array($type)) {
                $name    = $type[ 'name' ];
                $options = (array)$type;
            } else {
                $name = $type;
            }
            $type = new ApiDefinition($this, $name, $options);
        }
        $this->attributes[ 'api' ] = $type;

        return $this;
    }

    /** @param AttributeType|string $type */
    public function child($name, $type = null, $default = null, $apiType = null)
    {
        if ($name instanceof static) {
            $name->parent($this);
            $this->children->put($name->name, $name);
            return $name;
        }
        if ( ! $this->children->has($name)) {
            $this->children->put($name, with(new self($this))->name($name));
        }
        /** @var static $child */
        $child = $this->children->get($name);
        if ($type !== null) {
            $child->type($type);
        }
        if ($default !== null) {
            $child->default($default);
        }
        if ($apiType !== null) {
            $child->api($apiType);
        }
        return $child;
    }

    public function toArray()
    {
        $children = $this->children->toArray();
        return array_merge(collect($this->attributes)->toArray(), compact('children')); //        return array_merge(parent::toArray(), compact('children'));
    }

    public function getPath()
    {
        $segments = [ $this->name ];
        $parent   = $this->parent;
        while ($parent) {
            $segments[] = $parent->name;
            $parent     = $parent->parent;
        }
        return implode('.', array_reverse($segments));
    }

    public function hasParent()
    {
        return $this->parent !== null;
    }

    public function getChild(string $name)
    {
        if ( ! $this->hasChild($name)) {
            throw NotFoundException::definition($name, $this);
        }
        return $this->children->get($name);
    }

    public function hasChild(string $name)
    {
        return $this->children->has($name);
    }

    public function hasChildren()
    {
        return $this->children->isNotEmpty();
    }

    public function get($offset, $default = null)
    {
        return value(parent::get($offset, function () use ($offset, $default) {
            if ($this->hasChild($offset)) {
                return $this->getChild($offset);
            }
            return value($default);
        }));
    }

    /** @param string|string[] $value */
    public function inheritKeys($value)
    {
        if (is_string($value)) {
            $value = func_get_args();
        }
        $this->attributes[ 'inheritKeys' ] = array_unique(array_merge($this->get('inheritKeys', []), $value));
        return $this;
    }

    /** @param string|string[] $value */
    public function mergeKeys($value)
    {
        if (is_string($value)) {
            $value = func_get_args();
        }
        $this->attributes[ 'mergeKeys' ] = array_unique(array_merge($this->get('mergeKeys', []), $value));
        return $this;
    }

}
