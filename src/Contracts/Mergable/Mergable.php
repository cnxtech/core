<?php

namespace Codex\Contracts\Mergable;

interface Mergable
{
    public function getDefaultAttributes();

    public function getParentAttributes();

    public function getAttributes();

    public function setMergedAttributes(array $attributes);

    public function setChanged(array $keys);

    /** @return \Codex\Attributes\AttributeDefinitionGroup */
    public function getAttributeDefinitions();
}
