<?php

namespace Codex\Attributes;

class AttributeDefinition implements AttributeDefinitionParent
{
    use WithAttributeDefinitionChildren;

    /** @var string */
    public $name;

    /** @var AttributeDefinitionType */
    public $type;

    /** @var mixed|callable|null */
    public $default;

    /** @var AttributeDefinitionApiType */
    public $apiType;

    /**
     * Attribute constructor.
     *
     * @param string                                 $name
     * @param string|AttributeDefinitionType         $type
     * @param null|string|AttributeDefinitionApiType $apiType
     * @param callable|mixed|null                    $default
     *
     * @throws \Exception
     */
    public function __construct(string $name, $type, $apiType = null, $default = null)
    {
        $this->name    = $name;
        if($default !== null) {
            $this->default = $default;
        }
        if ( ! $type instanceof AttributeDefinitionType) {
            if (AttributeDefinitionType::isValid($type) === false) {
                throw new \Exception("Invalid attribute type {$type}");
            }
            $type = new AttributeDefinitionType($type);
        }
        if ($apiType === null) {
            $apiType = $type->toApiType();
        }
        if ( ! $apiType instanceof AttributeDefinitionApiType) {
            $apiType = new AttributeDefinitionApiType($apiType);
        }
        $this->type    = $type;
        $this->apiType = $apiType;
    }


    /**
     * Set the default value
     *
     * @param callable|mixed|null $default
     *
     * @return AttributeDefinition
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Set the apiType value
     *
     * @param string $apiType
     *
     * @return AttributeDefinition
     */
    public function setApiType($name, array $opts = [])
    {
        $this->apiType = new AttributeDefinitionApiType($name, $opts);
        return $this;
    }

    /**
     * Set the type value
     *
     * @param AttributeDefinitionType $type
     *
     * @return AttributeDefinition
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function hasDefault()
    {
        return isset($this->default);
    }

    public function resolveDefault()
    {
//        if (AttributeDefinitionType::ARRAY()->equals($this->type)) {
//            return $this->children;
//        }
        if ($this->default instanceof \Closure) {
            $closure = \Closure::bind($this->default, $this);
            return app()->call($closure);
        }
        return $this->default;
    }


}
