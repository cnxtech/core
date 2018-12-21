<?php

namespace Codex\Attributes;

class AttributeDefinitionApiType
{
    /** @var string */
    public $name;

    /** @var bool */
    public $extend = false;

    /** @var bool */
    public $array = false;

    /** @var bool */
    public $new = false;

    /** @var bool */
    public $nonNull = false;

    /** @var bool */
    public $arrayNonNull = false;

    public function __construct(string $name, array $opts = [])
    {
        $this->name = $name;
        $this->enableOptions($opts);
    }

    public function enableOptions(array $opts = [])
    {
        $this->extend       = \in_array('extend', $opts, true) ? true : $this->extend;
        $this->array        = \in_array('array', $opts, true) ? true : $this->array;
        $this->new          = \in_array('new', $opts, true) ? true : $this->new;
        $this->nonNull      = \in_array('nonNull', $opts, true) ? true : $this->nonNull;
        $this->arrayNonNull = \in_array('arrayNonNull', $opts, true) ? true : $this->arrayNonNull;
    }
}
