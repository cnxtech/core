<?php

namespace Codex\Models\Commands;

use Codex\Attributes\Commands\BuildDefinitionConfig;
use Codex\Contracts\Models\Model;
use Codex\Hooks;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Config\Definition\Processor;

class ProcessAttributes
{
    use DispatchesJobs;

    /** @var \Codex\Contracts\Models\Model */
    protected $target;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $parameters;

    public function __construct(Model $target, array $attributes = [], array $parameters = [])
    {
        $this->target     = $target;
        $this->attributes = $attributes;
        $this->parameters = $parameters;
    }

    public function handle()
    {
        $tree       = $this->dispatchNow(new BuildDefinitionConfig($this->target->getDefinition()));
        $processor  = new Processor();
        $attributes = $processor->process($tree, [ $this->attributes ]);
        $attributes = Hooks::waterfall('command.process_attributes', $attributes, [ $this->target ]);
        return $attributes;
    }

}
