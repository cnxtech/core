<?php


namespace Codex\Attributes;


use Illuminate\Support\Fluent;

/**
 * @property string  $name
 * @property boolean $nonNull
 * @property boolean $array
 * @property boolean $arrayNonNull
 * @property boolean $extend
 * @property boolean $new
 * @method $this name(string $name)
 * @method $this nonNull(boolean $nonNull = true)
 * @method $this array(boolean $array = true)
 * @method $this arrayNonNull(boolean $arrayNonNull = true)
 * @method $this extend(boolean $extend = true)
 * @method $this new(boolean $new = true)
 */
class ApiDefinition extends Fluent
{
    protected $definition;

    /**
     * ApiDefinition constructor.
     *
     * @param \Codex\Attributes\AttributeDefinition $definition
     * @param string|\Closure                       $name
     * @param array                                 $options
     */
    public function __construct(AttributeDefinition $definition, $name, array $options = [])
    {
        parent::__construct([]);
        $this->definition=$definition;
        $this->name($name)
            ->resetOptions()
            ->enableOptions($options);
    }

    public function getOptionKeys()
    {
        return [ 'nonNull', 'array', 'arrayNonNull', 'extend', 'new', ];
    }

    public function resetOptions()
    {
        foreach ($this->getOptionKeys() as $key) {
            $this->offsetSet($key, false);
        }
        return $this;
    }

    public function enableOptions(array $options = [])
    {
        foreach ($options as $option) {
            $this->offsetSet($option, true);
        }
        return $this;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function get($offset, $default = null)
    {
        return value(parent::get($offset,$default));
    }

}
