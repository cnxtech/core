<?php

namespace Codex\Console;

class AddonsEnableCommand extends AbstractAddonsCommand
{
    protected $signature = 'codex:addons:enable {name}';

    protected $description = 'Enables a addon';

    public function handle()
    {
        $this->callAddonManager('enable');
    }
}
