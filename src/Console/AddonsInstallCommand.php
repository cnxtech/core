<?php

namespace Codex\Console;

class AddonsInstallCommand extends AbstractAddonsCommand
{
    protected $signature = 'codex:addons:install {name}';

    protected $description = 'Installs a addon';

    public function handle()
    {
        $this->callAddonManager('install');
    }
}
