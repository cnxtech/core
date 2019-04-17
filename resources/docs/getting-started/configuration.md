---
title: Configuration
subtitle: Getting Started
---

# Configuration


### Inherited configuration

To view the available config options for each type you, use the `codex:config` command.
```bash
php artisan codex:config --groups # returns => [codex,projects,revisions,documents]
php artisan codex:config codex
php artisan codex:config codex.layout
php artisan codex:config projects
php artisan codex:config projects.layout
php artisan codex:config documents.title
# etc...
```

For example: <c-c cyan>Codex</c-c> has the config `layouts`,  which is inherited by <c-c deep-orange-7>Projects</c-c>


### Programmatic example
```php
$codex = codex();
$codex->attr('layout.header.color'); // blue-grey-5
$codex->setAttribute('layout.header.color', 'green-5');

$project = $codex->getProject('codex');
$project->attr('layout.header.color'); // green-5
```
