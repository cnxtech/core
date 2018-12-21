<?php /** @noinspection PhpIncludeInspection */

namespace Codex\Projects\Commands;

use Codex\Contracts\Projects\Project;
use Codex\Attributes\Commands\MergeAttributes;
use Codex\Projects\Events\ResolvedProject;
use Illuminate\Foundation\Bus\DispatchesJobs;

class MakeProject
{
    use DispatchesJobs;

    /**
     * @var string
     */
    protected $configFilePath;

    /**
     * MakeProject constructor.
     *
     * @param string $configFilePath
     */
    public function __construct($configFilePath)
    {
        $this->configFilePath = $configFilePath;
    }

    public function handle()
    {
        $path                 = $this->configFilePath;
        $attributes           = require $path;
        $attributes[ 'key' ]  = basename(dirname($path));
        $attributes[ 'path' ] = dirname($path);
        $project              = app(Project::class, compact('attributes'));
        $this->dispatch(new MergeAttributes($project));
        ResolvedProject::dispatch($project);

        return $project;
    }


}
