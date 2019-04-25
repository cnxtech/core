<?php

namespace Codex\Tests\Feature;

use Codex\Codex;
use Codex\Contracts\Projects\Project;
use Codex\Models\Commands\MergeAttributes;
use Codex\Projects\ProjectCollection;
use Codex\Tests\Fixture\CreatesConfigData;
use Codex\Tests\TestCase;
use Illuminate\Foundation\Bus\DispatchesJobs;

class FeatureTestCase extends TestCase
{
    use DispatchesJobs;
    use CreatesConfigData;

    protected function setUp()
    {
        $this->refreshApplication();
        $this->setUpCodexFromFixtures();

        parent::setUp();
        /** @var \Illuminate\Config\Repository $repository */
        $this->repository = $this->app->make('config');
        $this->config     = $this->createConfig();

        $this->repository->set('codex', data_get($this->configFileData, 'codex', []));
    }

    /** @var \Codex\Support\DotArrayWrapper */
    protected $config;

    /** @var \Illuminate\Config\Repository */
    protected $repository;


    protected function setUpCodexFromFixtures()
    {
        $this->app->afterResolving('codex', function (Codex $codex) {
            $projects = $codex->getProjects();
            $projects->setLoadable($paths = [
                'codex'            => __DIR__ . '/../Fixture/docs/codex/config.php',
                'blade-extensions' => __DIR__ . '/../Fixture/docs/blade-extensions/config.php',
            ]);
//            $projects->push($this->makeProject($paths[ 'codex' ]));
//            $projects->push($this->makeProject($paths[ 'blade-extensions' ]));
            $projects->setResolved(true);
            return $codex;
        });
    }


    protected function makeProject($path)
    {
        $attributes = require $path;
        $project    = app(Project::class, compact('attributes'));
        $this->dispatch(new MergeAttributes($project));
        return $project;
    }

}
