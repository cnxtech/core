<?php

namespace Codex\Attributes;

use Codex\Api\SchemaExtension;

class AttributeSchemaExtension extends SchemaExtension
{
    protected $provides = 'codex/core::schema.attributes';

    /** @var AttributeSchemaGenerator */
    protected $generator;

    public function __construct(AttributeSchemaGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function getSchemaExtension(): string
    {
        $generated = $this->generator->generate();

        return $generated;
    }
}
