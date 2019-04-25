<?php


namespace Codex\Attributes\Listener;


use Codex\Addons\Extensions\ExtensionCollection;
use Codex\Attributes\AttributeExtension;

class RegisterAttributeExtensions
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


    public function handle()
    {
        /** @var \Codex\Api\SchemaExtension[] $extensions */
//        $extensions = $this->extensions->search('codex/api::schema.*')->all();

        return $this->extensions
            ->search('codex/core::schema.*')
            ->map(function (AttributeExtension $extension) {
                return app()->call([$extension, 'register']);
            })
            ->implode("\n");
    }


}
