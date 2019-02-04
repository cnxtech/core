<?php

namespace Codex\Addons;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

//use Laradic\ServiceProvider\BaseServiceProvider;
//use Laradic\ServiceProvider\Plugins;

class AddonServiceProvider extends ServiceProvider
{
    use DispatchesJobs;

    /**
     * The provider class names.
     *
     * @var array
     */
    public $providers = [];

    /**
     * An array of the service provider instances.
     *
     * @var array
     */
    public $instances = [];

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    public $listen = [];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    public $subscribe = [];

    public $bindings = [];

    public $singletons = [];

    public $aliases = [];

    public $commands = [];

    public $provides = [];

    public $config = [];

    public $extensions = [];

    public $mapConfig = [];

    /** @var \Codex\Addons\Addon */
    protected $addon;

    /**
     * AddonServiceProvider constructor.
     */
    public function __construct($app, Addon $addon)
    {
        parent::__construct($app);
        $this->addon = $addon;
        $this->addResourcesHandler();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return $this->provides;
    }

    public function registerConfig()
    {
        foreach ($this->config as $key => $file) {
            if (is_int($key)) {
                $key  = $file;
                $file = 'config/' . $key;
            }
            $filePath = $this->addon->path($file . '.php');

            $this->mergeConfigFrom($filePath, $key);
            $this->publishes([
                $filePath => config_path(path_get_filename($filePath)),
            ]);
        }
    }


    /** @var */
    private $dir;

    /** @var */
    private $rootDir;

    /** @var string */
    protected $packagePath = '{rootDir}';

    /*
     |---------------------------------------------------------------------
     | Resources
     |---------------------------------------------------------------------
     |
     */

    /**
     * Path to resources directory.
     *
     * @var string
     */
    protected $resourcesPath = '{packagePath}/resources';

    /**
     * Resource destination path, by default uses laravel's 'resources' directory.
     *
     * @var string
     */
    protected $resourcesDestinationPath = '{path.resource}';

    /*
     |---------------------------------------------------------------------
     | Views
     |---------------------------------------------------------------------
     |
     */

    /**
     * View destination path, by default uses laravel's 'resources/views/vendor/{namespace}'.
     *
     * @var string
     */
    protected $viewsDestinationPath = '{resourcesDestinationPath}/views/vendor/{namespace}';

    /**
     * Package views path.
     *
     * @var string
     */
    protected $viewsPath = '{resourcesPath}/{dirName}';

    /**
     * A collection of directories in this package containing views.
     *
     * Using ['dirName' => 'namespace'] it binds the directory to a namespace.
     * This enables view('namespace::path.to.view') and includes it with vendor:publish
     *
     * @var array
     */
    protected $viewDirs = [/* 'dirName' => 'namespace' */ ];

    /*
     |---------------------------------------------------------------------
     | Assets
     |---------------------------------------------------------------------
     |
     */

    /**
     * Assets destination path.
     *
     * @var string
     */
    protected $assetsDestinationPath = '{path.public}/vendor/{namespace}';

    /**
     * Package assets path.
     *
     * @var string
     */
    protected $assetsPath = '{resourcesPath}/{dirName}';

    /**
     * A collection of directories in this package containing assets.
     * ['dirName' => 'namespace'].
     *
     * @var array
     */
    protected $assetDirs = [/* 'dirName' => 'namespace' */ ];

    /*
     |---------------------------------------------------------------------
     | Configuration
     |---------------------------------------------------------------------
     |
     */

    /**
     * Collection of configuration files.
     *
     * @var array
     */
    protected $configFiles = [];

    /** @var string */
    protected $configDestinationPath = '{path.config}';

    /**
     * Path to the config directory.
     *
     * @var string
     */
    protected $configPath = '{packagePath}/config';

    /*
     |---------------------------------------------------------------------
     | Translation
     |---------------------------------------------------------------------
     |
     */

    /** @var string */
    protected $translationDestinationPath = '{resourcesDestinationPath}/lang/vendor/{namespace}';

    /** @var string */
    protected $translationPath = '{resourcesPath}/{dirName}';

    /** @var array */
    protected $translationDirs = [/* 'dirName' => 'namespace', */ ];

    /*
     |---------------------------------------------------------------------
     | Database | Migrations | Seeds
     |---------------------------------------------------------------------
     |
     */

    /** @var string */
    protected $databaseDestinationPath = '{path.database}';

    /**
     * Path to database directory.
     *
     * @var string
     */
    protected $databasePath = '{packagePath}/database';

    /**
     * Path to the migration destination directory.
     *
     * @var string
     */
    protected $migrationDestinationPath = '{databaseDestinationPath}/migrations';

    /** @var string */
    protected $migrationsPath = '{databasePath}/{dirName}';

    /**
     * Array of directory names/paths relative to $databasePath containing migration files.
     *
     * @var array
     */
    protected $migrationDirs = [/* 'dirName', */ ];

    /**
     * Migrations will be loaded automaticly. If you want to publish the migrations, this should be true.
     *
     * @var bool
     */
    protected $publishMigrations = false;

    /**
     * Path to the seeds destination directory.
     *
     * @var string
     */
    protected $seedsDestinationPath = '{databaseDestinationPath}/seeds';

    /** @var string */
    protected $seedsPath = '{databasePath}/{dirName}';

    /**
     * Array of directory names/paths relative to $databasePath containing seed files.
     *
     * @var array
     */
    protected $seedDirs = [/* 'dirName', */ ];

    /**
     * startPathsPlugin method.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function addResourcesHandler()
    {
        $this->app->booted(function () {
            foreach ($this->viewDirs as $dirName => $namespace) {
                $viewPath = $this->resolvePath('viewsPath', compact('dirName'));
                $this->loadViewsFrom($viewPath, $namespace);
                $this->publishes([ $viewPath => $this->resolvePath('viewsDestinationPath', compact('namespace')) ], 'views');
            }

            foreach ($this->translationDirs as $dirName => $namespace) {
                $transPath = $this->resolvePath('translationPath', compact('dirName'));
                $this->loadTranslationsFrom($transPath, $namespace);
                $this->publishes([ $transPath => $this->resolvePath('translationDestinationPath', compact('namespace')) ], 'translations');
            }

            foreach ($this->assetDirs as $dirName => $namespace) {
                $this->publishes([
                    $this->resolvePath('assetsPath', compact('dirName')) => $this->resolvePath('assetsDestinationPath', compact('namespace')),
                ], 'public');
            }

            foreach ($this->migrationDirs as $dirName) {
                $migrationPaths = $this->resolvePath('migrationsPath', compact('dirName'));
                $this->loadMigrationsFrom($migrationPaths);
                if ($this->publishMigrations) {
                    $this->publishes([ $migrationPaths => $this->resolvePath('migrationDestinationPath') ], 'database');
                }
            }

            foreach ($this->seedDirs as $dirName) {
                $this->publishes([ $this->resolvePath('seedsPath', compact('dirName')) => $this->resolvePath('seedsDestinationPath') ], 'database');
            }
        });
    }

    /** @var */
    private $resolvedPaths;

    /**
     * resolvePath method.
     *
     * @todo
     *
     * @param       $pathPropertyName
     * @param array $extras
     *
     * @return string
     */
    public function resolvePath($name, array $extras = [])
    {
        if ($this->resolvedPaths === null) {
            $this->resolvedPaths = $this->getPaths();
        }
        if (str_contains($this->resolvedPaths[ $name ], [ '{', '}' ])) {
            preg_match_all('/{(.*?)}/', $this->resolvedPaths[ $name ], $matches);
            foreach ($matches[ 0 ] as $i => $match) {
                $var = $matches[ 1 ][ $i ];
                if (false === array_key_exists($var, $this->resolvedPaths)) {
                    continue;
                }
                $this->resolvedPaths[ $name ] = str_replace($match, $this->resolvePath($var, $extras), $this->resolvedPaths[ $name ]);
            }

            foreach ($extras as $key => $val) {
                $this->resolvedPaths[ $name ] = str_replace('{' . $key . '}', $val, $this->resolvedPaths[ $name ]);
            }
        }

        return $this->resolvedPaths[ $name ];
    }

    /**
     * getPaths method
     *
     * @return array
     */
    private function getPaths()
    {
        $paths = array_dot([ 'path' => $this->getLaravelPaths() ]);
        collect(array_keys(get_class_vars(get_class($this))))->filter(function ($propertyName) {
            return ends_with($propertyName, 'Path');
        })->each(function ($propertyName) use (&$paths) {
            $paths[ $propertyName ] = $this->{$propertyName};
        });
        $paths[ 'packagePath' ] = $this->getRootDir();

        return $paths;
    }

    /**
     * getLaravelPaths method
     *
     * @return array
     */
    private function getLaravelPaths()
    {
        $paths = [
            'app'     => $this->app[ 'path' ],
            'envFile' => $this->app->environmentFilePath(),
            'env'     => $this->app->environmentPath(),
            'cached'  => [
                'packages' => $this->app->getCachedPackagesPath(),
                'config'   => $this->app->getCachedConfigPath(),
                'routes'   => $this->app->getCachedRoutesPath(),
                'services' => $this->app->getCachedServicesPath(),
            ],
        ];
        foreach ([ 'base', 'lang', 'config', 'public', 'storage', 'database', 'bootstrap' ] as $key) {
            $paths[ $key ] = $this->app[ 'path.' . $key ];
        }
        $paths[ 'resource' ] = resource_path();

        return $paths;
    }

    private function getRootDir()
    {
        if($this->rootDir === null) {
            $class     = new ReflectionClass(get_called_class());
            $filePath  = $class->getFileName();
            $this->dir = $rootDir = path_get_directory($filePath);
            $found     = false;
            for ($i = 0; $i < 10; ++$i) {
                if (file_exists($composerPath = path_join($rootDir, 'composer.json'))) {
                    $found = true;
                    break;
                } else {
                    $rootDir = path_get_directory($rootDir); // go 1 up
                }
            }
            if ($found === false) {
                throw new \OutOfBoundsException("Could not determinse composer.json file location in [{$this->dir}] or in {$this->scanDirsMaxLevel} parents of [$this->rootDir}]");
            }
            $this->rootDir = $rootDir;
        }
        return $this->rootDir;
    }
}
