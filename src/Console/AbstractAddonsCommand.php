<?php

namespace Codex\Console;

use Codex\Addons\AddonManager;
use Illuminate\Console\Command;

class AbstractAddonsCommand extends Command
{
    protected function callAddonManager($operation, $name = null)
    {
        if ( ! \in_array($operation, [ 'install', 'uninstall', 'enable', 'disable' ], true)) {
            return $this->error("Invalid operation [{$operation}]");
        }
        if ($name === null) {
            $name = $this->argument('name');
        }
        $manager = $this->getLaravel()->make(AddonManager::class);
        $addons  = $manager->getAddons();
        if ( ! $addons->has($name)) {
            return $this->error("Addon [{$name}] does not exist");
        }
        $addon = $addons->get($name);
        $manager->$operation($addon);
        $this->line("{$operation}'d addon {$addon}");
    }
}
