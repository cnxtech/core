<?php

namespace Codex\Contracts\Models;

interface Mergable
{
    public function getDefaultAttributes();

    public function getParentAttributes();

    public function getAttributes();

    public function setMergedAttributes(array $attributes);

    /** @return \Codex\Attributes\AttributeDefinition */
    public function getDefinition();
}
