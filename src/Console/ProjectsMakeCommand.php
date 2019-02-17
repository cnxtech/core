<?php

namespace Codex\Console;

use Illuminate\Console\Command;

class ProjectsMakeCommand extends Command
{
    protected $signature = 'codex:projects:make {key}';

    protected $description = 'Generates a new project';

    public function handle()
    {
        $this->line('Done');
    }
}
