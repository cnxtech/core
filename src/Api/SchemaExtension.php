<?php

namespace Codex\Api;

use Codex\Addons\Extensions\Extension;

abstract class SchemaExtension extends Extension
{
    protected $provides = 'codex/core::schema.{name}';

    abstract public function getSchemaExtension(): string;
}
