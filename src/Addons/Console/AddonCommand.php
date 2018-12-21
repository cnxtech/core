<?php

namespace Codex\Addons\Console;

use Codex\Addons\AddonCollection;
use Codex\Addons\AddonManager;
use Illuminate\Console\Command;

class AddonCommand extends Command
{
    protected $signature = 'codex:addon {operation : install/uninstall/enable/disable/list} {name?}';

    public function handle(AddonCollection $addons, AddonManager $manager)
    {
        $operation = $this->argument('operation');
        if ($operation === 'list') {
            $rows = [];
            /** @var \Codex\Addons\Addon $addon */
            foreach ($addons as $addon) {
                $rows[] = [ $addon->getName(), $addon->isInstalled() ? 'Yes' : '', $addon->isEnabled() ? 'Yes' : '' ];
            }
            $this->table([ 'Name', 'Installed', 'Enabled' ], $rows);
            return;
        }

        if ( ! \in_array($operation, [ 'install', 'uninstall', 'enable', 'disable' ], true)) {
            return $this->error("Invalid operation [{$operation}]");
        }
        $name  = $this->argument('name');
        $addon = $addons->get($name);

        $manager->$operation($addon);

        $this->line("{$operation}'d addon {$addon}");
    }
}
