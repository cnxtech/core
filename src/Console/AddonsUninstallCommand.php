<?php

namespace Codex\Console;

class AddonsUninstallCommand extends AbstractAddonsCommand
{
    protected $signature = 'codex:addons:uninstall {name}';

    protected $description = 'Uninstalls a addon';

    public function handle()
    {
        $this->callAddonManager('uninstall');
    }
}
