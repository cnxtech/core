<?php

namespace Codex\Contracts\Mergable;

interface Mergable
{
    const CASTS_PATH = 'casts';
    const DEFAULTS_PATH = 'defaults';
    const INHERITS_PATH = 'inherits';

//    public function getMergableAttributesCasts();

    public function getDefaultAttributes();

//    public function getInheritableKeys();

    public function getParentAttributes();

    public function getAttributes();

    public function setMergedAttributes(array $attributes);

    /** @return \Codex\Attributes\AttributeDefinitionGroup */
    public function getAttributeDefinitions();
}
