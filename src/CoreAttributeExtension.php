<?php


namespace Codex;


use Codex\Attributes\AttributeExtension;
use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionRegistry;
use Codex\Attributes\AttributeType as T;

class CoreAttributeExtension extends AttributeExtension
{
    protected $name = 'core';

    public function register(AttributeDefinitionRegistry $registry)
    {
        $codex = $registry->codex;
        $codex->child('changes', T::ARRAY(T::STRING)); //->default([]);
        $cache = $codex->child('cache', T::MAP)->api('CacheConfig', [ 'new' ]);

        $cache->child('enabled', T::BOOL, true);
        $cache->child('key', T::STRING);
        $cache->child('minutes', T::INT);

        $codex->child('display_name', T::STRING, 'Codex')->required();
        $codex->child('description', T::STRING, '');
        $codex->child('default_project', T::STRING)->api('ID')->required();

        $urls = $codex->child('urls', T::MAP)->api('CodexUrls', [ 'new' ]);
        $urls->child('api', T::STRING);
        $urls->child('root', T::STRING);
        $urls->child('documentation', T::STRING);

        $paths = $codex->child('paths', T::MAP)->noApi();
        $paths->child('docs', T::STRING);
        $paths->child('log', T::STRING);

        $processors = $codex->child('processors', T::MAP)->noApi();
        $processors->child('enabled', T::MAP, []);

        $http = $codex->child('http', T::MAP)->api('HttpConfig', [ 'new' ]);
        $http->child('prefix', T::STRING);
        $http->child('api_prefix', T::STRING);
        $http->child('documentation_prefix', T::STRING);
        $http->child('documentation_view', T::STRING);
        $http->child('backend_data_url', T::STRING);

        $menu = new AttributeDefinition();
        $menu->name('menu')->type(T::RECURSIVE)->api('MenuItem', [ 'array', 'new' ]);
        $menu->child('id', T::STRING, function () {
            return md5(str_random());
        }, 'ID');
        $menu->child('type', T::STRING);
        $menu->child('class', T::STRING);
        $menu->child('side', T::STRING);
        $menu->child('target', T::STRING, 'self');
        $menu->child('href', T::STRING);
        $menu->child('path', T::STRING);
        $menu->child('renderer', T::STRING);
        $menu->child('expand', T::BOOL);
        $menu->child('selected', T::BOOL);
        $menu->child('label', T::STRING);
        $menu->child('sublabel', T::STRING);
        $menu->child('icon', T::STRING);
        $menu->child('color', T::STRING);
        $menu->child('project', T::STRING);
        $menu->child('revision', T::STRING);
        $menu->child('document', T::STRING);
        $menu->child('projects', T::BOOL);
        $menu->child('revisions', T::BOOL);
        $menu->child('children', T::RECURSE)->api('MenuItem', [ 'array' ]);

        //region: layout
        $layout                  = $codex->child('layout', T::MAP)->api('Layout', [ 'new' ]);
        $addLayoutPart           = static function (string $name, string $apiType) use ($layout) {
            $part = $layout->child($name, T::MAP)->api($apiType, [ 'new' ]);
            $part->child('class', T::MAP, []);
            $part->child('style', T::MAP, []);
            $part->child('color', T::STRING, null);
            $part->child('children', T::ARRAY(T::MAP), null, '[Assoc]'); //->api('LayoutToolbarItem', [ 'new'])->default([]);
            return $part;
        };
        $addLayoutHorizontalSide = static function (string $name, string $apiType) use ($addLayoutPart) {
            $part = $addLayoutPart($name, $apiType);
            $part->child('show', T::BOOL, true);
            $part->child('collapsed', T::BOOL, false);
            $part->child('outside', T::BOOL, true);
            $part->child('width', T::INT, 200);
            $part->child('collapsedWidth', T::INT, 50);
            $part->child('fixed', T::BOOL, false);
            return $part;
        };
        $addLayoutVerticalSide   = static function (string $name, string $apiType) use ($addLayoutPart) {
            $part = $addLayoutPart($name, $apiType);
            $part->child('show', T::BOOL, true);
            $part->child('fixed', T::BOOL, false);
            $part->child('height', T::INT, 64);
            return $part;
        };

        $layoutContainer = $addLayoutPart('container', 'LayoutContainer');
        $layoutContainer->child('stretch', T::BOOL, true);

        $layoutHeader = $addLayoutVerticalSide('header', 'LayoutHeader');
        $layoutHeader->children->put('menu', $menu);
        $layoutHeader->child('show_left_toggle', T::BOOL, false);
        $layoutHeader->child('show_right_toggle', T::BOOL, false);

        $layoutFooter = $addLayoutVerticalSide('footer', 'LayoutFooter');
        $layoutFooter->children->put('menu', $menu);
        $layoutFooter->child('text', T::STRING);

        $layoutLeft = $addLayoutHorizontalSide('left', 'LayoutLeft');
        $layoutLeft->children->put('menu', $menu);

        $layoutRight = $addLayoutHorizontalSide('right', 'LayoutRight');
        $layoutRight->children->put('menu', $menu);


        $layoutMiddle = $addLayoutPart('middle', 'LayoutMiddle');
        $layoutMiddle->child('padding', T::MIXED, 0);
        $layoutMiddle->child('margin', T::MIXED, 0);

        $layoutContent = $addLayoutPart('content', 'LayoutContent');
        $layoutContent->child('padding', T::MIXED, 0);
        $layoutContent->child('margin', T::MIXED, 0);

        $layoutToolbar = $addLayoutPart('toolbar', 'LayoutToolbar');
        $layoutToolbar->child('breadcrumbs', T::ARRAY(T::MAP));
        $layoutToolbar->child('left', T::ARRAY(T::MAP));
        $layoutToolbar->child('right', T::ARRAY(T::MAP));

        $projects = $registry->projects;
        $projects->mergeKeys([]);
        $projects->inheritKeys([ 'processors', 'layout', 'cache' ]);
        $projects->child('inherits', T::ARRAY(T::STRING), []);
        $projects->child('changes', T::MAP, []);
        $projects->child('key', T::STRING, null, 'ID!');
        $projects->child('path', T::STRING)->noApi();
        $projects->child('display_name', T::STRING, '');
        $projects->child('description', T::STRING, '');
        $projects->child('disk', T::STRING, null);
        $projects->child('view', T::STRING, 'codex::document');

        $meta = $projects->child('meta', T::MAP)->api('Meta', [ 'new' ]);
        $meta->child('icon', T::STRING, 'fa-book');
        $meta->child('color', T::STRING, 'deep-orange');
        $meta->child('license', T::STRING, 'MIT');

        $meta->child('defaultTitle', T::STRING);
        $meta->child('title', T::STRING);
        $meta->child('titleTemplate', T::STRING);
        $meta->child('titleAttributes', T::MAP);
        $meta->child('htmlAttributes', T::MAP);
        $meta->child('bodyAttributes', T::MAP);
        $metaLink   = $meta->child('link', T::MAP, []);
        $metameta   = $meta->child('meta', T::MAP, []);
        $metaScript = $meta->child('script', T::ARRAY(T::STRING), []);
        $metaStyle  = $meta->child('style', T::ARRAY(T::STRING), []);

        $projects->child('default_revision', T::STRING, 'master');
        $projects->child('allow_revision_php_config', T::STRING, false);
        $projects->child('allowed_revision_config_files', T::ARRAY(T::STRING));

        $projects->child('default_document', T::STRING, 'index');
        $projects->child('document_extensions', T::ARRAY(T::STRING), []);

        $revisions = $registry->revisions;
        $revisions->mergeKeys([]);
        $revisions->inheritKeys([ 'processors', 'meta', 'layout', 'view', 'cache', 'default_document', 'document_extensions' ]);
        $revisions->child('inherits', T::ARRAY(T::STRING), []);
        $revisions->child('changes', T::MAP, []);
        $revisions->child('key', T::STRING, null, 'ID!');

        $documents = $registry->documents;
        $documents->mergeKeys([]);
        $documents->inheritKeys([ 'processors', 'meta', 'layout', 'view', 'cache' ]);
        $documents->child('inherits', T::ARRAY(T::STRING), []);
        $documents->child('changes', T::MAP, []);
        $documents->child('key', T::STRING, null, 'ID!');
        $documents->child('path', T::STRING);
        $documents->child('extension', T::STRING);
        $documents->child('content', T::STRING);
        $documents->child('last_modified', T::INT);
        $documents->child('title', T::STRING, '');
        $documents->child('subtitle', T::STRING, '');
        $documents->child('description', T::STRING, '');
        $documents->child('scripts', T::ARRAY(T::STRING), []);
        $documents->child('styles', T::ARRAY(T::STRING), []);
        $documents->child('html', T::ARRAY(T::STRING), []);
    }


}
