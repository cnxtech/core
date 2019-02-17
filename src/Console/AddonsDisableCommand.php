<?php

namespace Codex\Console;

class AddonsDisableCommand extends AbstractAddonsCommand
{
    protected $signature = 'codex:addons:disable {name}';

    protected $description = 'Disables a addon';

    public function handle()
    {
        $this->callAddonManager('disable');
    }
}
