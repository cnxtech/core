<?php

namespace Codex;

use Codex\Addons\Extensions\RegisterExtension;
use Codex\Attributes\AttributeDefinitionFactory;
use Codex\Contracts\Documents\Document;
use Codex\Documents\Events\ResolvedDocument;
use Codex\Documents\Listeners\ProcessDocument;
use Codex\Log\Log;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laradic\ServiceProvider\ServiceProvider;
use League\Flysystem\Filesystem as Flysystem;
use Radic\BladeExtensions\BladeExtensionsServiceProvider;

class CodexServiceProvider extends ServiceProvider
{
    use DispatchesJobs;

    protected $strict = false;

    protected $configPluginPriority = [ 5, 10 ];

    protected $providersPluginPriority = 45;

    protected $configFiles = [ 'codex', 'codex.layout', 'codex.processor-defaults' ];

    protected $viewDirs = [ 'views' => 'codex' ];

    protected $commands = [
        Addons\Console\AddonCommand::class,
    ];

    public $singletons = [
        Codex::class                                  => Codex::class,
        Addons\AddonManager::class                    => Addons\AddonManager::class,
        Addons\AddonCollection::class                 => Addons\AddonCollection::class,
        Addons\AddonRegistry::class                   => Addons\AddonRegistry::class,
        Addons\Extensions\ExtensionCollection::class  => Addons\Extensions\ExtensionCollection::class,
        Attributes\AttributeDefinitionRegistry::class => Attributes\AttributeDefinitionRegistry::class,
        Attributes\ConfigResolverRegistry::class      => Attributes\ConfigResolverRegistry::class,
    ];

    public $providers = [
        Api\ApiServiceProvider::class,
        Http\HttpServiceProvider::class,
        BladeExtensionsServiceProvider::class,
    ];

    public $bindings = [
        'codex'                             => Codex::class,
        'codex.addons'                      => Addons\AddonCollection::class,
        'codex.extensions'                  => Addons\Extensions\ExtensionCollection::class,
        'codex.attributes'                  => Attributes\AttributeDefinitionRegistry::class,
        'codex.attributes.config'           => Attributes\ConfigResolverRegistry::class,
        Contracts\Projects\Project::class   => Projects\Project::class,
        Contracts\Revisions\Revision::class => Revisions\Revision::class,
        Contracts\Documents\Document::class => Documents\Document::class,
    ];

    protected $listen = [
        ResolvedDocument::class => [
            ProcessDocument::class,
        ],
    ];

    protected $extensions = [
        Documents\Processors\AttributesProcessorExtension::class,
        Documents\Processors\ParserProcessorExtension::class,
        Documents\Processors\CacheProcessorExtension::class,
        Documents\Processors\LinksProcessorExtension::class,
        Documents\Processors\MacrosProcessorExtension::class,
        Documents\Processors\TocProcessorExtension::class,
        Documents\Processors\HeaderProcessorExtension::class,
        Attributes\AttributeSchemaExtension::class,
    ];

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

        $this->registerAttributeDefinitions();

        $manager = $this->app->make(Addons\AddonManager::class);
        $manager->register();

        $this->dispatch(new RegisterExtension($this->extensions));
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

    protected function registerAttributeDefinitions()
    {
        $registry = $this->app->make(Attributes\AttributeDefinitionRegistry::class);
        $codex    = $registry->codex;
        $codex->add('changes', 'dictionary', 'Assoc');

        $codex->add('display_name', 'string')->setDefault('Codex');
        $codex->add('description', 'string')->setDefault('');
        $codex->add('default_project', 'string', 'ID')->setDefault(null);

        $urls = $codex->add('urls', 'dictionary')->setApiType('CodexUrls', [ 'new' ]);
        $urls->add('api', 'string');
        $urls->add('root', 'string');
        $urls->add('documentation', 'string');

        $paths = $codex->add('paths', 'dictionary')->noApi();
        $paths->add('docs', 'string');
        $paths->add('log', 'string');

        $processors = $codex->add('processors', 'dictionary')->noApi(); //->setApiType('Processors', [ 'new' ]);
        $processors->add('enabled', 'array.scalarPrototype');

        $http = $codex->add('http', 'dictionary')->setApiType('HttpConfig', [ 'new' ]);
        $http->add('prefix', 'string');
        $http->add('api_prefix', 'string');
        $http->add('documentation_prefix', 'string');
        $http->add('documentation_view', 'string');

        $menu = AttributeDefinitionFactory::attribute('menu', 'array.recursive')->setApiType('MenuItem', [ 'array', 'new' ]);
        $menu->add('id', 'string', 'ID', function () {
            return md5(str_random());
        });
        $menu->add('type', 'string');
        $menu->add('side', 'string');
        $menu->add('target', 'string')->setDefault('self');
        $menu->add('href', 'string');
        $menu->add('path', 'string');
        $menu->add('renderer', 'string');
        $menu->add('expand', 'boolean');
        $menu->add('selected', 'boolean');
        $menu->add('label', 'string');
        $menu->add('sublabel', 'string');
        $menu->add('icon', 'string');
        $menu->add('color', 'string');
        $menu->add('project', 'string');
        $menu->add('revision', 'string');
        $menu->add('document', 'string');
        $menu->add('projects', 'boolean');
        $menu->add('revisions', 'boolean');
        $menu->add('children', 'recurse')->setApiType('MenuItem', [ 'array' ]); //->children = $menu->children;

        $layout                  = $codex->add('layout', 'dictionary')->setApiType('Layout', [ 'new' ]);
        $addLayoutPart           = function (string $name, string $apiType) use ($layout) {
            $part = $layout->add($name, 'dictionary')->setApiType($apiType, [ 'new' ]);
            $part->add('class', 'array.scalarPrototype');
            $part->add('style', 'array.scalarPrototype')->setDefault([]);
            $part->add('color', 'string')->setDefault(null);
            return $part;
        };
        $addLayoutHorizontalSide = function (string $name, string $apiType) use ($addLayoutPart, $menu) {
            $part = $addLayoutPart($name, $apiType);
            $part->add('show', 'boolean')->setDefault(true);
            $part->add('collapsed', 'boolean')->setDefault(false);
            $part->add('outside', 'boolean')->setDefault(true);
            $part->add('width', 'integer')->setDefault(200);
            $part->add('collapsedWidth', 'integer')->setDefault(50);
            $part->addChild($menu);
            return $part;
        };
        $addLayoutVerticalSide   = function (string $name, string $apiType) use ($addLayoutPart, $menu) {
            $part = $addLayoutPart($name, $apiType);
            $part->add('show', 'boolean')->setDefault(true);
            $part->add('fixed', 'boolean')->setDefault(false);
            $part->add('height', 'integer')->setDefault(64);
            $part->addChild($menu);
            return $part;
        };

        $layoutContainer = $addLayoutPart('container', 'LayoutContainer');
        $layoutHeader    = $addLayoutVerticalSide('header', 'LayoutHeader');
        $layoutFooter    = $addLayoutVerticalSide('footer', 'LayoutFooter');
        $layoutLeft      = $addLayoutHorizontalSide('left', 'LayoutLeft');
        $layoutRight     = $addLayoutHorizontalSide('right', 'LayoutRight');
        $layoutMiddle    = $addLayoutPart('middle', 'LayoutMiddle');
        $layoutContent   = $addLayoutPart('content', 'LayoutContent');

        $layoutHeader->add('show_left_toggle', 'boolean')->setDefault(false);
        $layoutHeader->add('show_right_toggle', 'boolean')->setDefault(false);
        $layoutContainer->add('stretch', 'boolean')->setDefault(true);
        $layoutMiddle->add('padding', 'mixed', 'Mixed', 0);
        $layoutMiddle->add('margin', 'mixed', 'Mixed', 0);
        $layoutContent->add('padding', 'mixed', 'Mixed', 0);
        $layoutContent->add('margin', 'mixed', 'Mixed', 0);


        $projects = $registry->projects;
        $projects->add('inherits', 'array.scalarPrototype', '[String]');
        $projects->addInheritKeys([ 'processors', 'layout' ]);
        $projects->add('changes', 'dictionary', 'Assoc');
        $projects->add('key', 'string', 'ID!');
        $projects->add('path', 'string')->noApi();
        $projects->add('display_name', 'string')->setDefault('');
        $projects->add('description', 'string')->setDefault('');
        $projects->add('disk', 'string')->setDefault(null);
        $projects->add('view', 'string')->setDefault('codex::document');

        $cache = $projects->add('cache', 'dictionary')->setApiType('Cache', [ 'new' ]);
        $cache->add('mode', 'string')->setDefault(null);
        $cache->add('minutes', 'integer')->setDefault(7);

        $meta = $projects->add('meta', 'dictionary')->setApiType('Meta', [ 'new' ]);
        $meta->add('icon', 'string')->setDefault('fa-book');
        $meta->add('color', 'string')->setDefault('deep-orange');
        $meta->add('license', 'string')->setDefault('MIT');
//        $meta->add('stylesheets', 'array.scalarPrototype');
//        $meta->add('javascripts', 'array.scalarPrototype');
//        $meta->add('styles', 'array.scalarPrototype');
//        $meta->add('scripts', 'array.scalarPrototype');

        $meta->add('defaultTitle', 'string');
        $meta->add('title', 'string');
        $meta->add('titleTemplate', 'string');
        $meta->add('titleAttributes', 'dictionary', 'Assoc');
        $meta->add('htmlAttributes', 'dictionary', 'Assoc');
        $meta->add('bodyAttributes', 'dictionary', 'Assoc');
        $metaLink   = $meta->add('link', 'dictionaryPrototype', '[Assoc]', []);
        $metameta   = $meta->add('meta', 'dictionaryPrototype', '[Assoc]', []);
        $metaScript = $meta->add('script', 'array.scalarPrototype', '[String]', []);
        $metaStyle  = $meta->add('style', 'array.scalarPrototype', '[String]', []);


//        $revision = $projects->add('revision', 'dictionary')->setApiType('RevisionConfig', [ 'new' ]);
        $projects->add('default_revision', 'string')->setDefault('master');
        $projects->add('allow_revision_php_config', 'string')->setDefault(false);
        $projects->add('allowed_revision_config_files', 'array.scalarPrototype');

//        $document = $projects->add('document', 'dictionary')->setApiType('DocumentConfig', [ 'new' ]);
        $projects->add('default_document', 'string')->setDefault('index');
        $projects->add('document_extensions', 'array.scalarPrototype');


        $revisions = $registry->revisions;
        $revisions->addMergeKeys([]);
        $revisions->addInheritKeys([ 'processors', 'meta', 'layout', 'view', 'cache', 'default_document', 'document_extensions' ]);
        $revisions->add('inherits', 'array.scalarPrototype', '[String]');
        $revisions->add('changes', 'dictionary', 'Assoc');
        $revisions->add('key', 'string', 'ID!');

        $documents = $registry->documents;
        $documents->addMergeKeys([]);
        $documents->addInheritKeys([ 'processors', 'meta', 'layout', 'view', 'cache' ]);
        $documents->add('inherits', 'array.scalarPrototype', '[String]');
        $documents->add('changes', 'dictionary', 'Assoc');
        $documents->add('key', 'string', 'ID!');
        $documents->add('path', 'string');
        $documents->add('extension', 'string');
        $documents->add('content', 'string');
        $documents->add('last_modified', 'integer');
        $documents->add('title', 'string')->setDefault('');
        $documents->add('subtitle', 'string')->setDefault('');
        $documents->add('description', 'string')->setDefault('');
    }
}
