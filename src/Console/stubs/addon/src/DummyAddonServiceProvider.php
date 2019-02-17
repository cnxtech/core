<?php

/** @noinspection RepetitiveMethodCallsInspection */

namespace AddonNamespace;

use Codex\Addons\AddonServiceProvider;
use Codex\Attributes\AttributeDefinitionRegistry;

class DummyAddonServiceProvider extends AddonServiceProvider
{
    public $config = [ ':vendor:-:name:' ];

    public $mapConfig = [ ':vendor:-:name:.default_project_config' => 'codex.projects' ];

    protected $viewDirs = ['views' => ':vendor:-:name:'];

    protected $assetDirs = ['views' => ':vendor:-:name:'];

    public function register()
    {
    }

    public function boot(AttributeDefinitionRegistry $registry)
    {
        $projects = $registry->projects;
        $projects->add(':name:', 'dictionary')->setApiType(':Name:Config', [ 'new' ]);
    }
}
