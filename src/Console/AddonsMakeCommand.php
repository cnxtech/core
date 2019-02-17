<?php

namespace Codex\Console;

use Codex\Addons\AddonCollection;
use Codex\Support\StubGenerator;
use Illuminate\Console\Command;

class AddonsMakeCommand extends Command
{
    protected $signature = 'codex:addons:make 
                            {vendor : The vendor name}
                            {name : The addon name}
                            {--p|path= : The output path}';

    protected $description = 'Create a new addon';

    public function handle(AddonCollection $addons)
    {
        $vendor = $this->argument('vendor');
        $name   = $this->argument('name');
        $path   = $this->option('path');
        if ( ! $path) {
            $path = base_path($vendor . '/' . $name);
        }
        if (path_is_relative($path)) {
            $path = base_path($path);
        }

        $replacements = [
            ':vendor:'       => $vendor,
            ':Vendor:'       => ucfirst($vendor),
            ':name:'         => $name,
            ':Name:'         => ucfirst($name),
            ':package:'      => $vendor . '/' . $name,
            ':psr4:'         => studly_case($vendor) . '\\\\' . studly_case($name) . '\\\\',
            'Dummy'          => studly_case($name),
            'AddonNamespace' => studly_case($vendor) . '\\' . studly_case($name),
        ];

        StubGenerator::create(__DIR__ . '/stubs/addon/composer.json', $path . '/composer.json', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/README.md', $path . '/README.md', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/.editorconfig', $path . '/.editorconfig', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/.gitignore', $path . '/.gitignore', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/src/DummyAddon.php', $path . '/src/' . $replacements[ 'Dummy' ] . 'Addon.php', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/src/DummyAddonServiceProvider.php', $path . '/src/' . $replacements[ 'Dummy' ] . 'AddonServiceProvider.php', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/resources/assets/dummy.js', $path . '/resources/assets/index.js', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/resources/views/dummy.blade.php', $path . '/resources/views/index.blade.php', $replacements);
        StubGenerator::create(__DIR__ . '/stubs/addon/config/dummy.php', $path . '/config/' . $replacements[ ':vendor:' ] . '-' . $replacements[ ':name:' ] . '.php', $replacements);

        $this->line('Created addon files in ' . $path);
    }
}
