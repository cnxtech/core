<?php

namespace Codex;

use Codex\Addons\Extensions\RegisterExtension;
use Codex\Concerns\ProvidesResources;
use Codex\Contracts\Documents\Document;
use Codex\Documents\Events\ResolvedDocument;
use Codex\Documents\Listeners\ProcessDocument;
use Codex\Log\Log;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\{Arr, Collection};
use Laradic\ServiceProvider\ServiceProvider;
use League\Flysystem\Filesystem as Flysystem;


class CodexServiceProvider extends ServiceProvider
{
    use ProvidesResources;
    use DispatchesJobs;

    protected $strict = false;

    protected $configPluginPriority = [ 5, 10 ];

    protected $providersPluginPriority = 45;

    protected $configFiles = [ 'codex', 'codex.layout', 'codex.processor-defaults' ];

    protected $viewDirs = [ 'views' => 'codex' ];

    protected $assetDirs = [ 'assets' => 'codex_core' ];

    protected $commands = [
        \Codex\Console\AddonsMakeCommand::class,
        \Codex\Console\AddonsListCommand::class,
        \Codex\Console\AddonsDisableCommand::class,
        \Codex\Console\AddonsEnableCommand::class,
        \Codex\Console\AddonsUninstallCommand::class,
        \Codex\Console\AddonsInstallCommand::class,
        \Codex\Console\ProjectsMakeCommand::class,
        \Codex\Console\ConfigCommand::class,
    ];

    public $singletons = [
        Codex::class                                         => Codex::class,
        \Codex\Addons\AddonManager::class                    => \Codex\Addons\AddonManager::class,
        \Codex\Addons\AddonCollection::class                 => \Codex\Addons\AddonCollection::class,
        \Codex\Addons\AddonRegistry::class                   => \Codex\Addons\AddonRegistry::class,
        \Codex\Addons\Extensions\ExtensionCollection::class  => \Codex\Addons\Extensions\ExtensionCollection::class,
        \Codex\Attributes\AttributeDefinitionRegistry::class => \Codex\Attributes\AttributeDefinitionRegistry::class,
    ];

    public $providers = [
        \Codex\Api\ApiServiceProvider::class,
        \Codex\Http\HttpServiceProvider::class,
        \Radic\BladeExtensions\BladeExtensionsServiceProvider::class,
    ];

    public $bindings = [
        'codex'                                    => Codex::class,
        'codex.addons'                             => \Codex\Addons\AddonCollection::class,
        'codex.extensions'                         => \Codex\Addons\Extensions\ExtensionCollection::class,
        'codex.attributes'                         => \Codex\Attributes\AttributeDefinitionRegistry::class,
        \Codex\Contracts\Projects\Project::class   => \Codex\Projects\Project::class, //Contracts\Projects\Project::class
        \Codex\Contracts\Revisions\Revision::class => \Codex\Revisions\Revision::class, //Contracts\Revisions\Revision::class
        \Codex\Contracts\Documents\Document::class => \Codex\Documents\Document::class, //Contracts\Documents\Document::class
    ];

    protected $listen = [
        ResolvedDocument::class => [
            ProcessDocument::class,
        ],
    ];

    protected $extensions = [
        \Codex\Documents\Processors\AttributesProcessorExtension::class,
        \Codex\Documents\Processors\ParserProcessorExtension::class,
        \Codex\Documents\Processors\CacheProcessorExtension::class,
        \Codex\Documents\Processors\LinksProcessorExtension::class,
        \Codex\Documents\Processors\MacrosProcessorExtension::class,
        \Codex\Documents\Processors\TocProcessorExtension::class,
        \Codex\Documents\Processors\HeaderProcessorExtension::class,
        \Codex\Documents\Processors\ButtonsProcessorExtension::class,
        \Codex\Attributes\AttributeSchemaExtension::class
    ];

    protected $middleware = [ Http\DebugbarCollectionLoggerMiddleware::class ];

    /**
     * boot method
     *
     * @return \Illuminate\Contracts\Foundation\Application
     * @throws \Exception
     */
    public function boot()
    {
        $app = parent::boot();
        $app[ 'events' ]->listen(ResolvedDocument::class, function (ResolvedDocument $event) {
            $document = $event->getDocument();
            $document->on('process', function (Document $document) {
                $document->push('meta.meta', [ 'name' => 'title', 'content' => $document->attr('title') ]);
                $description = $document->attr('description', $document->getProject()->attr('description', $document->getCodex()->attr('description')));
                $document->push('meta.meta', [ 'name' => 'description', 'content' => $description ]);
                $document->push('meta.htmlAttributes.lang', config('app.locale'));
            });
        });

        return $app;
    }
    

    public function booting()
    {
        $this->dispatchNow(new RegisterExtension(CoreAttributeExtension::class));

        $manager = $this->app->make(Addons\AddonManager::class);
        $manager->register();

        $this->dispatchNow(new RegisterExtension($this->extensions));
    }

    /**
     * register method
     *
     * @return \Illuminate\Contracts\Foundation\Application
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \ReflectionException
     */
    public function register()
    {
        Arr::mixin(new Support\ArrMixin());
        Collection::mixin(new Support\CollectionMixin());
        $app = parent::register();
        $this->registerLogger();
        $this->registerDefaultFilesystem();

        return $app;
    }

    public function registerLogger()
    {
        $this->app->singleton('codex.log', function () {
            $logger = new Log();
            $logger->useFiles($this->app[ 'config' ][ 'codex.paths.log' ]);
            $logger->useFirePHP();
            $logger->useChromePHP();
            return $logger;
        });
    }

    protected function registerDefaultFilesystem()
    {
        $fsm = $this->app->make('filesystem');

        $fsm->extend('codex-local', function (Application $app, array $config = []) {
            $adapter   = new Filesystem\Local($config[ 'root' ]);
            $flysystem = new Flysystem($adapter);
            return new FilesystemAdapter($flysystem);
        });
    }
}
