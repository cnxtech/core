<?php

namespace Codex\Attributes;

use Codex\Api\SchemaExtension;
use Codex\Attributes\Commands\BuildDefinitionSchema;
use Illuminate\Foundation\Bus\DispatchesJobs;

class AttributeSchemaExtension extends SchemaExtension
{
    use DispatchesJobs;

    protected $provides = 'codex/core::schema.attributes';

    public function getSchemaExtension(): string
    {
        $generated = $this->dispatchNow(new BuildDefinitionSchema());

        return $generated;
    }
}
