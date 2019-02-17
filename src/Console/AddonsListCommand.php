<?php

namespace Codex\Console;

use Codex\Addons\AddonCollection;
use Illuminate\Console\Command;

class AddonsListCommand extends Command
{
    protected $signature = 'codex:addons:list';

    protected $description = 'Lists all addons';

    public function handle(AddonCollection $addons)
    {
        $rows = [];
        /** @var \Codex\Addons\Addon $addon */
        foreach ($addons as $addon) {
            $rows[] = [ $addon->getName(), $addon->isInstalled() ? 'Yes' : '', $addon->isEnabled() ? 'Yes' : '' ];
        }
        $this->table([ 'Name', 'Installed', 'Enabled' ], $rows);
    }
}
