<?php

namespace Codex\Console;

use Codex\Attributes\AttributeDefinition;
use Illuminate\Console\Command;

class ConfigCommand extends Command
{
    protected $signature = 'codex:config {path?}';

    public function handle()
    {
        $path     = $this->argument('path');
        $types    = [];
        $inherits = [
            'projects'  => 'codex',
            'revisions' => 'projects',
            'documents' => 'revisions',
        ];
        $registry = codex()->getRegistry();
        foreach ($registry->keys() as $key) {
            $group = $registry->getGroup($key);
            data_set($types, $key, []);

            if (array_key_exists($key, $inherits)) {
                $fromKey = $inherits[ $key ];
                $from    = $registry->getGroup($fromKey);
                foreach ($group->inheritKeys as $inheritKey) {
                    $inherited = data_get($types[ $fromKey ], $inheritKey);
                    data_set($types[ $key ], $inheritKey, $inherited);
                }
            }

            foreach ($group->getChildren() as $child) {
                $this->group($child, $types[ $key ]);
            }
        }
        if ($path) {
            $types = data_get($types, $path, []);
        }
        foreach (array_dot($types) as $key => $value) {
            $this->line("<info>{$key}</info>: <comment>{$value}</comment>");
        }
    }

    protected function group(AttributeDefinition $definition, &$types)
    {
        if ($definition->hasChildren()) {
            data_set($types, $definition->name, []);
            foreach ($definition->getChildren() as $child) {
                $this->group($child, $types[ $definition->name ]);
            }
        } else {
            data_set($types, $definition->name, $definition->type->toApiType());
        }
    }
}
