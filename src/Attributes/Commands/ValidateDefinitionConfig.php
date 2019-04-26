<?php


namespace Codex\Attributes\Commands;


use Codex\Attributes\AttributeDefinition;
use Illuminate\Support\MessageBag;

class ValidateDefinitionConfig
{
    /** @var \Codex\Attributes\AttributeDefinition */
    protected $definition;

    /**
     * @var array
     */
    protected $config;

    protected $rules;

    public function __construct(AttributeDefinition $definition, array $config)
    {
        $this->definition = $definition;
        $this->config     = $config;
    }

    public function handle()
    {
        $this->rules = [];
        $this->handleDefinition($this->definition);
        $errors = new MessageBag();
        foreach ($this->rules as $key => $rules) {
            foreach($rules as $rule) {
                $errors->merge($this->validate(data_get($this->config, $key), $rule));
            }
        }

        return $errors->toArray();
    }

    protected function validate($value, $rules)
    {
        $validator = validator([ 'value' => $value ], [ 'value', $rules ]);

        if ($validator->fails()) {
            return $validator->errors();
        }
        return $validator->getMessageBag();
    }

    /**
     * @param \Illuminate\Support\Collection|AttributeDefinition[] $children
     */
    protected function recurse($children)
    {
        foreach ($children as $child) {
            $this->handleDefinition($child);
        }
    }

    protected function handleDefinition(AttributeDefinition $definition)
    {
        $name   = $definition->name;
        $prefix = $this->definition->getPath();
        $path   = $definition->getPath();
        $path   = str_remove_left($path, $prefix);
        $path   = str_remove_left($path, '.');

        if ($definition->validation) {
            $rules                = array_wrap($definition->validation);
            $this->rules[ $path ] = $rules;
        }
        if ($definition->hasChildren()) {
            $this->recurse($definition->children);
        }
    }
}
