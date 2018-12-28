<?php

namespace Codex\Api;

use Codex\Api\GraphQL\QueryDirectiveRegistry;
use Codex\Api\Listeners\AttachSchemaExtensions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Nuwave\Lighthouse\Events\BuildingAST;
use Nuwave\Lighthouse\Schema\DirectiveRegistry;

class ApiServiceProvider extends EventServiceProvider
{
    public $listen = [
        BuildingAST::class => [
            AttachSchemaExtensions::class,
        ],
    ];

    public function register()
    {
        $overrides = [
            'route_name'      => $this->app[ 'config' ][ 'codex.http.api_prefix' ],
            'cache'           => [
                'enable' => env('LIGHTHOUSE_CACHE_ENABLE', false),
                'key'    => env('LIGHTHOUSE_CACHE_KEY', 'lighthouse-schema'),
            ],
            'controller'      => \Codex\Http\Controllers\ApiController::class . '@query',
            'schema.register' => __DIR__ . '/../../routes/schema.graphqls',
            'extensions'      => [
//                \Nuwave\Lighthouse\Schema\Extensions\TracingExtension::class,
            ],
            'error_handlers'  => [ \Nuwave\Lighthouse\Execution\ExtensionErrorHandler::class ],
            'namespaces'      => [
                'models'    => 'Codex\\Api\\GraphQL\\Models',
                'mutations' => 'Codex\\Api\\GraphQL\\Mutations',
                'queries'   => 'Codex\\Api\\GraphQL\\Queries',
                'scalars'   => 'Codex\\Api\\GraphQL\\Scalars',
            ],
        ];
        foreach ($overrides as $key => $value) {
            $this->app[ 'config' ][ "lighthouse.{$key}" ] = $value;
        }

        $this->app->singleton(GraphQL\QueryDirectiveRegistry::class);
        $this->app->bind(\Nuwave\Lighthouse\Schema\SchemaBuilder::class, GraphQL\SchemaBuilder::class);
        $this->app->register(\Nuwave\Lighthouse\Providers\LighthouseServiceProvider::class, true);
        $this->app->singleton(GraphQL\GraphQL::class);
        $this->app->alias(GraphQL\GraphQL::class, 'graphql');
        $this->app->register(\DeInternetJongens\LighthouseUtils\ServiceProvider::class);
    }

    public function boot()
    {
        parent::boot();
        $this->registerDirectives();
        $this->registerQueryDirectives();
    }

    protected function registerDirectives()
    {
        $classes  = [
//            GraphQL\Directives\AssocDirective::class,
            GraphQL\Directives\FilterDirective::class,
            GraphQL\Directives\PagesDirective::class,
            GraphQL\Directives\ConstraintsDirective::class,
            GraphQL\Directives\DefaultValueDirective::class,
        ];
        $registry = resolve(DirectiveRegistry::class);
        foreach ($classes as $directiveClass) {
            $registry->register(resolve($directiveClass));
        }
    }

    protected function registerQueryDirectives()
    {
        $classes  = [
            GraphQL\QueryDirectives\AssocQueryDirective::class,
        ];
        $registry = resolve(QueryDirectiveRegistry::class);
        foreach ($classes as $directiveClass) {
            $registry->register(resolve($directiveClass));
        }
    }
}
