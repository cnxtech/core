<?php /** @noinspection PhpIncludeInspection */

namespace Codex\Projects\Commands;

use Codex\Contracts\Projects\Project;
use Codex\Hooks;
use Codex\Mergable\Commands\MergeAttributes;
use Codex\Projects\Events\ResolvedProject;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ResolveProject
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

    public function handle(Repository $cache)
    {
        $path                 = $this->configFilePath;
        $attributes           = require $path;
        $attributes[ 'key' ]  = basename(dirname($path));
        $attributes[ 'path' ] = dirname($path);
        $project              = app(Project::class, compact('attributes'));
        $project->setConfigFilePath($path);

        if ($project->attr('cache.enabled', $project->getCodex()->attr('cache.enabled'))) {
            $keyPrefix       = $project->attr('cache.key', $project->getCodex()->attr('cache.key')) . '.project.attributes.' . $project->getKey();
            $lastModifiedKey = $keyPrefix . '.lastModified';
            $dataKey         = $keyPrefix . '.data';

//        $cache->forget($lastModifiedKey);
//        $cache->forget($dataKey);

            $lastModified = $project->getLastModified();
            if ($lastModified > $cache->get($lastModifiedKey, 0)) {
                $cache->forget($dataKey);
            }

            $cache->put($lastModifiedKey, $lastModified, 5);
            $attr = $cache->remember($dataKey, 5, function (...$args) use ($project) {
                $this->dispatch(new MergeAttributes($project));
                return $project->getAttributes();
            });
            $project->setRawAttributes($attr);
        } else {
            $this->dispatch(new MergeAttributes($project));
        }
        $project->updateDisk();


        Hooks::run('project.resolved', [ $this ]);
        $project->fireEvent('resolved', $project);
        ResolvedProject::dispatch($project);
        return $project;
    }
}
