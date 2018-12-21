<?php

namespace Codex\Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $path = __DIR__ . '/../bootstrap/app.php';
        if ( ! file_exists($path)) {
            $path = __DIR__ . '/../../../bootstrap/app.php';
        }
        $app = require $path;

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
