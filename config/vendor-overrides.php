<?php


return [
    'lighthouse'       => [
//        'route_name'       => 'graphql',
//        'route_enable_get' => true,
        'route'      => [
            'prefix' => '',
            // 'middleware' => ['web','api'],    // [ 'loghttp']
        ],
        'schema'     => [
            'register' => __DIR__ . '/../routes/graphql/schema.graphqls',
        ],
//        'cache'            => [
//            'enable' => env('LIGHTHOUSE_CACHE_ENABLE', false),
//            'key'    => env('LIGHTHOUSE_CACHE_KEY', 'lighthouse-schema'),
//        ],
//        'directives' => [ __DIR__ . '/../src/Api/Directives' ],
        'namespaces' => [
            'models'    => 'Codex\\Api\\GraphQL\\Models',
            'mutations' => 'Codex\\Api\\GraphQL\\Mutations',
            'queries'   => 'Codex\\Api\\GraphQL\\Queries',
            'scalars'   => 'Codex\\Api\\GraphQL\\Scalars',
        ],
//        'security'         => [
//            'max_query_complexity'  => 0,
//            'max_query_depth'       => 0,
//            'disable_introspection' => DisableIntrospection::DISABLED,
//        ],
        'controller' => 'Nuwave\Lighthouse\Support\Http\Controllers\GraphQLController@query',

        'global_id_field' => '_id',
    ],
    'lighthouse-utils' => [
        'schema_paths' => [
            'mutations' => __DIR__ . '/../src/Api/Mutations',
            'queries'   => __DIR__ . '/../src/Api/Queries',
            'types'     => __DIR__ . '/../src/Api/Types',
        ],
    ],
    'rinvex'           => [
        'attributes' => [
            // Attributes Database Tables
            'tables' => [
                'attributes'                => 'attributes',
                'attribute_entity'          => 'attribute_entity',
                'attribute_boolean_values'  => 'attribute_boolean_values',
                'attribute_datetime_values' => 'attribute_datetime_values',
                'attribute_integer_values'  => 'attribute_integer_values',
                'attribute_text_values'     => 'attribute_text_values',
                'attribute_varchar_values'  => 'attribute_varchar_values',

            ],

            // Attributes Models
            'models' => [
                'attribute'        => \Codex\Database\Attributes\Attribute::class,
                'attribute_entity' => \Codex\Database\Attributes\AttributeEntity::class,
            ],
        ],
    ],
];
