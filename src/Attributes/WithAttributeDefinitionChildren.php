<?php


namespace Codex\Attributes;


trait WithAttributeDefinitionChildren
{
    /** @var AttributeDefinition[] */
    public $children = [];

    /**
     * add method
     *
     * @param string                         $name
     * @param string|AttributeDefinitionType $type
     * @param string|null                    $apiType
     * @param null                           $default
     *
     * @return AttributeDefinition
     */
    public function add(string $name, $type, $apiType = null, $default = null)
    {
        return $this->addChild(AttributeDefinitionFactory::attribute($name, $type, $apiType, $default));
    }

    public function addChild(AttributeDefinition $attribute)
    {
        $this->children[ $attribute->name ] = $attribute;
        return $attribute;
    }

    public function hasChildren()
    {
        return count($this->children);
    }

    public function hasChild(string $name)
    {
        return array_key_exists($name, $this->children);
    }

    public function getChild(string $name)
    {
        return $this->children[ $name ];
    }

}
