<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace Codex\Models;

use ArrayAccess;
use Closure;
use Codex\Attributes\AttributeDefinition;
use Codex\Concerns\HasCallbacks;
use Codex\Concerns\HasContainer;
use Codex\Concerns\HasEvents;
use Codex\Concerns\Macroable;
use Codex\Contracts\Models\ChildInterface;
use Codex\Contracts\Models\Mergable;
use Codex\Contracts\Models\Model as ModelContract;
use Codex\Contracts\Models\ParentInterface;
use Codex\Hooks;
use Codex\Models\Commands\GetChangedAttributes;
use Codex\Models\Concerns\HasMergableAttributes;
use Codex\Models\Concerns\HasRelations;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use JsonSerializable;
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;

abstract class Model implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, Mergable, ModelContract
{
    use DispatchesJobs;
    use HasContainer;
    use HasRelations;
    use HidesAttributes;
    use HasMergableAttributes;


    use HasEvents;
    use HasCallbacks;
    use Macroable {
        __call as __callMacro;
        __callStatic as __callStaticMacro;
    }

    /** @var \Codex\Models\Storage */
    protected $storage;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public function getClassSlug()
    {
        return str_slug(last(explode('\\', static::class)));
    }

    public function show($attributes)
    {
        $attributes = is_string($attributes) ? func_get_args() : $attributes;
        $attributes = Arr::explodeToPaths($attributes);
        foreach ($attributes as $attribute) {
            if (head($attribute) === '*') {
                $attribute = $this->getAttributeKeys();
            }
            foreach ($attribute as $keys) {
                $keys = explode('.', $keys);
                $key  = array_shift($keys);

                $this->makeVisible($key);

                $related   = $this->getRelationValue($key);
                $isRelated = $related instanceof EloquentCollection || $related instanceof Model;
                $isRelated = \count($keys) > 0 && $isRelated;
                if ($isRelated) {
                    $attribute = implode('.', $keys);
                    $related->show($attribute);
                }
            }
        }
        return $this;
    }

    public function rehide($recursive = false)
    {
        $except = array_merge($this->getVisible(), $this->getHidden(), [ $this->getKeyName() ]);
        $hidden = array_keys(array_except($this->attributes, $except));
        $this->addHidden($hidden);

        if ($recursive) {
            foreach ($this->getRelations() as $key => $relation) {
                if (method_exists($relation, 'rehide')) {
                    $relation->rehide($recursive);
                }
                $this->makeHidden($key);
            }
        }
        return $this;
    }

    public function getGraphSelection($attributes)
    {
        $this->show($attributes);
        $data = $this->toArray();
        $this->rehide(true);
        return $data;
    }

    public function attr($key, $default = null)
    {
        $this->show($key);
        if ($this->hasAttribute($key)) {
            $data = $this->getAttribute($key);
        } else {
            $data = data_get($this, $key, $default);
        }
        $this->rehide(true);
        return $data;
    }

    public function set($key, $value)
    {
        data_set($this->attributes, $key, $value);
        return $this;
    }

    public function push($key, $value)
    {
        /** @var array $data */
        $data = $this->attr($key, []);
        if (is_array($data)) {
            $data[] = $value;
            data_set($this->attributes, $key, $data);
        }
        return $this;
    }


    protected $enabled = true;

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isDisabled()
    {
        return ! $this->enabled;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function disable()
    {
        return $this->setEnabled(false);
    }

    public function enable()
    {
        return $this->setEnabled(true);
    }


    //region: Init

    public static $snakeAttributes = true;

    protected static $booted = [];

    protected static $traitInitializers = [];

    protected $initialized = false;

    /**
     * @var \Codex\Models\ParameterPostProcessor
     */
    protected $parameterProcessor;


    public function initialize(array $attributes = [], AttributeDefinition $definition = null)
    {
        if ($this->initialized) {
            return $this;
        }

        if ($definition !== null) {
            $this->definition = $definition;
        }

        $this->storage = new Storage($this);

        $this->bootIfNotBooted();

        $this->initializeTraits();

        $attributes = Hooks::waterfall('model.initialize', $attributes, [ $this ]);

        $this->setRawAttributes($attributes);

        $this->initialized = true;

        Hooks::run('model.initialized', [ $this ]);

        $this->fireEvent('initialized', $this);

        return $this;
    }

    protected function initializeParameterPostProcessor($value)
    {
        if ($this->parameterProcessor === null) {
            $app = app();

//            $parameters                          = is_array($value) ? $value : compact('value');
            $parameters[ 'app' ]                 = $app;
            $parameters[ 'config' ]              = $app[ 'config' ];
            $parameters[ $this->getClassSlug() ] = $this;
            $parameters[ 'this' ]                = $this;

            $target = $this;
            while ($target instanceof ChildInterface) {
                $name                = $target->getClassSlug();
                $parameters[ $name ] = $target;
                $target              = $target->getParent();
            }
            $this->parameterProcessor = new ParameterPostProcessor($parameters, true);
        }
        $this->parameterProcessor->setParameter('value', $value);
        return $this->parameterProcessor;
    }


    protected function bootIfNotBooted()
    {
        if ( ! isset(static::$booted[ static::class ])) {
            static::$booted[ static::class ] = true;

            $this->fireEvent('booting', $this);

            static::boot();

            $this->fireEvent('booted', $this);
        }
    }

    protected static function boot()
    {
        static::bootTraits();
    }

    protected static function bootTraits()
    {
        $class = static::class;

        $booted = [];

        static::$traitInitializers[ $class ] = [];

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot' . class_basename($trait);

            if (method_exists($class, $method) && ! in_array($method, $booted)) {
                forward_static_call([ $class, $method ]);

                $booted[] = $method;
            }

            if (method_exists($class, $method = 'initialize' . class_basename($trait))) {
                static::$traitInitializers[ $class ][] = $method;

                /** @noinspection SlowArrayOperationsInLoopInspection */
                static::$traitInitializers[ $class ] = array_unique(
                    static::$traitInitializers[ $class ]
                );
            }
        }
    }

    protected function initializeTraits()
    {
        foreach (static::$traitInitializers[ static::class ] as $method) {
            $this->{$method}();
        }
    }

    public static function clearBootedModels()
    {
        static::$booted = [];
    }

    //endregion


    //region: Events

    public static function initialized(callable $callback)
    {
        return static::onEvent('initialized', $callback);
    }

    public static function booting(callable $callback)
    {
        return static::onEvent('initialized', $callback);
    }

    public static function booted(callable $callback)
    {
        return static::onEvent('initialized', $callback);
    }

    public static function resolved(callable $callback)
    {
        return static::onEvent('resolved', $callback);
    }

    //endregion


    //region: Attributes

    /** @var AttributeDefinition */
    protected $definition;

    protected $attributes = [];

    protected $callAttributeClosures = false;

    public function getChanges()
    {
        if ($this instanceof ChildInterface) {
            return $this->dispatch(new GetChangedAttributes($this, true, [ 'changes' ]));
        }
        return [];
    }

    /** @return  AttributeDefinition */
    public function getDefinition()
    {
        return $this->definition;
    }

    public function getInheritKeys()
    {
        return $this->definition->inheritKeys;
    }

    public function setRawAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function attributesToArray()
    {
        $attributes = $this->addMutatedAttributesToArray(
            $attributes = $this->getArrayableAttributes(), $mutatedAttributes = $this->getMutatedAttributes()
        );

        // Here we will grab all of the appended, calculated attributes to this model
        // as these attributes are not really in the attributes array, but are run
        // when we need to array or JSON the model for convenience to the coder.
        foreach ($this->getArrayableAppends() as $key) {
            data_set($attributes, $key, $this->mutateAttributeForArray($key, null));
        }

        return $attributes;
    }

    public function hasAttribute($key)
    {

        return array_has($this->attributes, $key) || $this->hasGetMutator($key) || array_key_exists($key, $this->getAttributeKeys());
    }

    public function getAttribute($key)
    {
        if ( ! $key) {
            return;
        }

        // Dot notation support.
        if (array_has($this->attributes, $key) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    public function getAttributeValue($key)
    {
        $value = $this->getRawAttributeValue($key);
        $value = $this->processAttributeValue($key, $value);
        return $value;
    }

    /** get unprocessed attribute value */
    public function getRawAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    protected function processAttributeValue($key, $value)
    {
        if ($this->parameterProcessor === null) {
            $app                                 = app();
            $parameters                          = [];
            $parameters[ 'app' ]                 = $app;
            $parameters[ 'config' ]              = $app[ 'config' ];
            $parameters[ $this->getClassSlug() ] = $this;
            $parameters[ 'this' ]                = $this;
            $target                              = $this;
            while ($target instanceof ChildInterface) {
                $name                = $target->getClassSlug();
                $parameters[ $name ] = $target;
                $target              = $target->getParent();
            }
            $this->parameterProcessor = new ParameterPostProcessor($parameters, true);
            $this->parameterProcessor->setModel($this);
        }
        $this->parameterProcessor->setParameter('value', $value);
        $agg = new ConfigAggregator(
            [ new ArrayProvider(compact('value')) ],
            storage_path('asdf.php'), // @todo implement cache
            [ $this->parameterProcessor ]
        );
        return $agg->getMergedConfig()[ 'value' ]; //        $value = data_get($agg->getMergedConfig(), 'value', $value);
    }

    public function callClosures(bool $value = true)
    {
        $this->callAttributeClosures = $value;
        return $this;
    }

    protected function getAttributeFromArray($key)
    {
        $data = data_get($this->attributes, $key);

        if ($this->callAttributeClosures) {
            if ($data instanceof Closure) {
                $data = app()->call($data, [ $this, $this->getContainer() ]);
            }
            if (is_array($data)) {
                return $this->callAttributeChildClosures($data);
            }
        }
        return $data;
    }

    protected function callAttributeChildClosures($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[ $key ] = $this->callAttributeChildClosures($value);
            }
            if ($value instanceof Closure) {
                $data[ $key ] = app()->call($value, [ $this, $this->getContainer() ]);
            }
        }
        return $data;
    }

    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        data_set($this->attributes, $key, $value);

        return $this;
    }

    protected function getArrayableAttributes()
    {
        return $this->getArrayableItems($this->attributes);
    }

    protected function getArrayableAppends()
    {
        if ( ! count($this->appends)) {
            return [];
        }

        return $this->getArrayableItems(
            array_combine($this->appends, $this->appends)
        );
    }

    protected function getArrayableItems(array $values)
    {
        if (count($this->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisible()));
        }

        if (count($this->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHidden()));
        }

        return $values;
    }

    public function getAttributeKeys()
    {
        return array_unique(array_merge(array_keys($this->attributes), $this->appends, $this->getMutatedAttributes()));
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    //endregion


    //region: Relations

    public function relationsToArray()
    {
        $attributes = [];

        foreach ($this->getArrayableRelations() as $key => $value) {
            // If the values implements the Arrayable interface we can just call this
            // toArray method on the instances which will convert both models and
            // collections to their proper array form and we'll set the values.
            if ($value instanceof Arrayable) {
                $relation = $value->toArray();
            }

            // If the value is null, we'll still go ahead and set it in this list of
            // attributes since null is used to represent empty relationships if
            // if it a has one or belongs to type relationships on the models.
            elseif ($value === null) {
                $relation = $value;
            }

            // If the relationships snake-casing is enabled, we will snake case this
            // key so that the relation attribute is snake cased in this returned
            // array to the developers, making this consistent with attributes.
            if (static::$snakeAttributes) {
                $key = snake_case($key);
            }

            // If the relation value has been set, we will set it on this attributes
            // list for returning. If it was not arrayable or null, we'll not set
            // the value on the array because it is some type of invalid value.
            if ($relation !== null || $value === null) {
                $attributes[ $key ] = $relation;
            }

            unset($relation);
        }

        return $attributes;
    }

    protected function getArrayableRelations()
    {
        return $this->getArrayableItems($this->relations);
    }

    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[ $key ];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }

    protected function getRelationshipFromMethod($method)
    {
        $relation = $this->$method();
        $this->setRelation($method, $relation);
        return $relation;
    }

    //endregion


    //region: Mutation

    protected $getMutators = [];

    protected $setMutators = [];

    protected $appends = [];

    public function append($attributes)
    {
        $this->appends = array_unique(
            array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
        );

        return $this;
    }

    public function setAppends(array $appends)
    {
        $this->appends = $appends;

        return $this;
    }

    protected function addMutatedAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        foreach ($mutatedAttributes as $key) {
            if (str_contains($key, '.')) {
                if ( ! array_has($attributes, $key)) {
                    continue;
                }

                data_set($attributes, $key, $this->mutateAttributeForArray(
                    $key, data_get($attributes, $key)
                ));
                continue;
            }

            // We want to spin through all the mutated attributes for this model and call
            // the mutator for the attribute. We cache off every mutated attributes so
            // we don't have to constantly check on attributes that actually change.
            if ( ! array_key_exists($key, $attributes)) {
                continue;
            }

            // Next, we will call the mutator for this attribute so that we can get these
            // mutated attribute's actual values. After we finish mutating each of the
            // attributes we will return this final array of the mutated attributes.
            $attributes[ $key ] = $this->mutateAttributeForArray(
                $key, $attributes[ $key ]
            );
        }

        return $attributes;
    }

    public function addGetMutator($key, $mutatorMethod, $append = false, $hide = true)
    {
        $this->getMutators[ $key ] = $this->createMutatorMethod($mutatorMethod);
        if ($append) {
            $this->append($key);
            if ($hide) {
                $this->makeHidden($key);
            }
        }
        return $this;
    }

//    public function getDynamicSetMutator($key)
//    {
//        return $this->setMutators[ $key ];
//    }

    public function addSetMutator($key, $mutatorMethod)
    {
        $this->setMutators[ $key ] = $this->createMutatorMethod($mutatorMethod);
        return $this;
    }

    protected function createMutatorMethod($mutatorMethod)
    {
        if (\is_string($mutatorMethod) && method_exists($this, $mutatorMethod)) {
            $method = function ($value) use ($mutatorMethod) {
                return $this->$mutatorMethod($value);
            };
            $method->bindTo($this);
            return $method;
        }
        if ($mutatorMethod instanceof \Closure) {
            return $mutatorMethod->bindTo($this);
        }
    }

    protected function callDynamicGetMutator($key, $value)
    {
        if ($this->hasDynamicGetMutator($key)) {
            $mutator = $this->getDynamicGetMutator($key);
            return $mutator($value);
        }
    }

    protected function callMethodGetMutator($key, $value)
    {
        if ($this->hasMethodGetMutator($key)) {
            return $this->{'get' . studly_case($key) . 'Attribute'}($value);
        }
    }

    protected function callDynamicSetMutator($key, $value)
    {
        if ($this->hasDynamicSetMutator($key)) {
            return $this->setMutators[ $key ]($value);
        }
    }

    protected function callMethodSetMutator($key, $value)
    {
        if ($this->hasMethodSetMutator($key)) {
            return $this->{'set' . studly_case($key) . 'Attribute'}($value);
        }
    }

    public function hasGetMutator($key)
    {
        return $this->hasDynamicGetMutator($key) || $this->hasMethodGetMutator($key);
    }

    public function hasMethodGetMutator($key)
    {
        return method_exists($this, 'get' . studly_case($key) . 'Attribute');
    }

    public function hasDynamicGetMutator($key)
    {
        if (strpos($key, '.') === false) {
            return array_key_exists($key, $this->getMutators);
        }

        $current = $key;
        $sliced  = [];
        foreach (array_reverse(explode('.', $key)) as $part) {
            if (array_key_exists($current, $this->getMutators)) {
                return true;
            }
            $current  = str_remove_right($current, '.' . $part);
            $sliced[] = $part;
        }
        return false;
    }


    public function getDynamicGetMutator($key)
    {
        if (strpos($key, '.') === false) {
            return $this->getMutators[ $key ];
        }
        $current = $key;
        $sliced  = [];
        foreach (array_reverse(explode('.', $key)) as $part) {
            if (array_key_exists($current, $this->getMutators)) {
                $mutator = $this->getMutators[ $current ];
                if (empty($sliced)) {
                    return $mutator;
                }
                return function ($value) use ($mutator, $sliced) {
                    $value = $mutator($value);
                    return data_get($value, implode('.', $sliced));
                };
                break;
            }
            $current  = str_remove_right($current, '.' . $part);
            $sliced[] = $part;
        }


        return null;
    }

    protected function getClosestParentDynamicGetMutator($key)
    {

    }

    public function hasSetMutator($key)
    {
        return $this->hasDynamicSetMutator($key) || $this->hasMethodSetMutator($key);
    }

    protected function hasMethodSetMutator($key)
    {
        return method_exists($this, 'set' . studly_case($key) . 'Attribute');
    }

    protected function hasDynamicSetMutator($key)
    {
        return array_key_exists($key, $this->setMutators);
    }

    protected function setMutatedAttributeValue($key, $value)
    {
        if ($this->hasDynamicSetMutator($key)) {
            return $this->callDynamicSetMutator($key, $value);
        }
        return $this->callMethodSetMutator($key, $value);
    }

    protected function mutateAttribute($key, $value)
    {
        if ($this->hasDynamicGetMutator($key)) {
            return $this->callDynamicGetMutator($key, $value);
        }
        return $this->callMethodGetMutator($key, $value);
    }

    protected function mutateAttributeForArray($key, $value)
    {
        $value = $this->mutateAttribute($key, $value);

        return $value instanceof Arrayable ? $value->toArray() : $value;
    }

    protected static $mutatorCache = [];

    public function getMutatedAttributes()
    {
        $class = static::class;

        if ( ! isset(static::$mutatorCache[ $class ])) {
            static::cacheMutatedAttributes($class);
        }

        return array_merge(
            static::$mutatorCache[ $class ],
            array_keys($this->getMutators)
        );
    }

    public static function cacheMutatedAttributes($class)
    {
        static::$mutatorCache[ $class ] = collect(static::getMutatorMethods($class))->map(function ($match) {
            return lcfirst(static::$snakeAttributes ? snake_case($match) : $match);
        })->all();
    }

    protected static function getMutatorMethods($class)
    {
        preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches);

        return $matches[ 1 ];
    }

    //endregion


    //region: Getters/Setters

    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getKeyType()
    {
        return $this->keyType;
    }

    public function newCollection(array $models = [])
    {
        if ($this instanceof ChildInterface) {
            $parent = $this->getParent();
            if ($parent instanceof ParentInterface) {
                $class = get_class($parent->getChildren());
                return new $class($models, $parent);
            }
        }
        return new Collection($models);
    }

    /**
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    public function storage($key, callable $value)
    {
        return $this->storage->getSet($key, $value);
    }

    //endregion


    //region: Implemented Interface Methods

    /**
     * Convert the model instance to an array.
     *
     * @param string|string[] $show
     *
     * @param bool            $rehide
     *
     * @return array
     */
    public function toArray($show = null, $rehide = true)
    {
        if ($show !== null) {
            $this->show($show);
        }
        $data = array_merge($this->attributesToArray(), $this->relationsToArray());
        if ($show !== null && $rehide) {
            $this->rehide(true);
        }
        return $data;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     *
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getAttribute($offset) !== null;
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[ $offset ], $this->relations[ $offset ]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     *
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        /** @var string $key */
        $key = $this->getKey();
        return $key;
    }

    /**
     * When a model is being unserialized, check if it needs to be booted.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->bootIfNotBooted();
    }

    public function __call($name, $arguments)
    {
        if (starts_with($name, 'get')) {
            $key = str_remove_left($name, 'get');
            $key = kebab_case($key);
            if (\in_array($key, $this->getAttributeKeys(), true)) {
                return $this->getAttribute($key);
            }
        }
        if (static::hasMacro($name)) {
            return $this->__callMacro($name, $arguments);
        }
        throw new \BadMethodCallException("Could not find method [{$name}]");
    }

    //endregion
}
