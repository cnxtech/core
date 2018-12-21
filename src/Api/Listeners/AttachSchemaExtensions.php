<?php

namespace Codex\Api\Listeners;

use Codex\Addons\Extensions\ExtensionCollection;
use Codex\Api\SchemaExtension;
use Nuwave\Lighthouse\Events\BuildingAST;

class AttachSchemaExtensions
{
    /** @var \Codex\Addons\Extensions\ExtensionCollection */
    protected $extensions;

    /**
     * AttachSchemaExtensions constructor.
     *
     * @param \Codex\Addons\Extensions\ExtensionCollection $extensions
     */
    public function __construct(ExtensionCollection $extensions)
    {
        $this->extensions = $extensions;
    }


    public function handle(BuildingAST $event)
    {
        /** @var \Codex\Api\SchemaExtension[] $extensions */
//        $extensions = $this->extensions->search('codex/api::schema.*')->all();

        return $this->extensions
            ->search('codex/api::schema.*')
            ->map(function (SchemaExtension $extension) {
                return $extension->getSchemaExtension();
            })
            ->implode("\n");
    }
}
