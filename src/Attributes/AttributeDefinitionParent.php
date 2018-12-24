<?php


namespace Codex\Attributes;


interface AttributeDefinitionParent
{
    public function add(string $name, $type, $apiType = null, $default = null);

    public function addChild(AttributeDefinition $attribute);

    public function hasChildren();

    public function hasChild(string $name);

    public function getChild(string $name);

    /**
     * @return \Codex\Attributes\AttributeDefinition[]
     */
    public function getChildren();
}
