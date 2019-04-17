---
title: Core Concepts
subtitle: Getting Started
---

# Core Concepts


### Application flow

<pre><strong><c-c cyan>Codex</c-c> > <c-c deep-orange-7>Projects</c-c> > <c-c teal-7>Revisions</c-c> > <c-c pink-7>Documents</c-c></strong></pre> 

- <c-c cyan>Codex</c-c> has one or more <c-c deep-orange-7>Projects</c-c>
- Each <c-c deep-orange-7>Project</c-c> has one or more <c-c teal-7>Revisions</c-c> <small>(branches/versions)</small>
- Each <c-c teal-7>Revision</c-c> contains <c-c pink-7>Documents</c-c>
- Each <c-c pink-7>Document</c-c> is passed trough several processors, modifying the output

### Directory Structure
<!--*codex:row*-->
<!--*codex:col({md: 6})*-->
<pre>- <c-c cyan>docs</c-c>
  - [<c-c deep-orange-7>project</c-c>]
    - <c-c >config.php</c-c>
    - [<c-c teal-7>revision</c-c>]
      - <c-c >revision.yml</c-c>
      - [<c-c pink-7>document</c-c>]
      - [<c-c>directory</c-c>]
        - [<c-c pink-7>document</c-c>]
</pre>
<!--*codex:/col*-->
<!--*codex:col({md: 6})*-->
<pre>- <c-c deep-orange-7>codex</c-c>
    - <c-c >config.php</c-c>
    - <c-c teal-7>master</c-c>
      - <c-c >revision.yml</c-c>
      - <c-c pink-7>index.md</c-c>
      - <c-c>getting-started</c-c>
        - <c-c pink-7>installation.md</c-c>
        - <c-c pink-7>configuration.md</c-c>
    - <c-c teal-7>2.1.0</c-c>
      - <c-c >revision.yml</c-c>
      - <c-c pink-7>index.md</c-c>
</pre>
<!--*codex:/col*-->

<!--*codex:col({md: 6})*-->
3
<!--*codex:/col*-->

<!--*codex:col({md: 6})*-->
4
<!--*codex:/col*-->
<!--*codex:/row*-->



## Models f

### Variant 1
[Codex](#codex:phpdoc['Codex\Codex']:popover:drawer:!icon), 
[Project](#codex:phpdoc['Codex\Projects\Project']:popover:drawer:!icon), 
[Revision](#codex:phpdoc['Codex\Revisions\Revision']:popover:drawer:!icon), 
[Document](#codex:phpdoc['Codex\Documents\Document']:popover:drawer:!icon) 

implement [Project](#codex:phpdoc['Codex\Contracts\Projects\Project']:popover:drawer:!icon), 
[Revision](#codex:phpdoc['Codex\Contracts\Revisions\Revision']:popover:drawer:!icon), 
[Document](#codex:phpdoc['Codex\Contracts\Documents\Document']:popover:drawer:!icon) 

and extend the [Model](#codex:phpdoc['Codex\Mergable\Model']:popover:drawer:!icon) class. 

[Model](#codex:phpdoc['Codex\Mergable\Model']:popover:drawer:!icon) classes use 
[GetChangedAttributes](#codex:phpdoc['Codex\Mergable\Concerns\GetChangedAttributes']:popover:drawer:!icon), 
[HasMergableAttributes](#codex:phpdoc['Codex\Mergable\Concerns\HasMergableAttributes']:popover:drawer:!icon), 
[HasRelations](#codex:phpdoc['Codex\Mergable\Concerns\HasRelations']:popover:drawer:!icon)


### Variant 2

[Codex](#codex:phpdoc['Codex\Codex']:popover:drawer), 
[Project](#codex:phpdoc['Codex\Projects\Project']:popover:drawer), 
[Revision](#codex:phpdoc['Codex\Revisions\Revision']:popover:drawer), 
[Document](#codex:phpdoc['Codex\Documents\Document']:popover:drawer) 

implement [Project](#codex:phpdoc['Codex\Contracts\Projects\Project']:popover:drawer), 
[Revision](#codex:phpdoc['Codex\Contracts\Revisions\Revision']:popover:drawer), 
[Document](#codex:phpdoc['Codex\Contracts\Documents\Document']:popover:drawer) 

and extend the [Model](#codex:phpdoc['Codex\Mergable\Model']:popover:drawer) class. 

[Model](#codex:phpdoc['Codex\Mergable\Model']:popover:drawer) classes use 
[GetChangedAttributes](#codex:phpdoc['Codex\Mergable\Concerns\GetChangedAttributes']:popover:drawer), 
[HasMergableAttributes](#codex:phpdoc['Codex\Mergable\Concerns\HasMergableAttributes']:popover:drawer), 
[HasRelations](#codex:phpdoc['Codex\Mergable\Concerns\HasRelations']:popover:drawer)



## Flow
```php
// Get the `Codex` instance
$codex = codex();
$codex = resolve('codex');
$codex = resolve(\Codex\Codex::class);

// `Codex` populates its attributes using several of the `codex.php` configuration values
$codex->getAttributes();
  
// Returns the `ProjectCollection`, dispatches `FindProjects` once
$projects = $codex->getProjects();

// Dispatches `ResolveProject` once for every first unique get() call. `ResolveProject` 
// 1. `Project` receives its default attributes from `$codex['projects']`
// 2. `Project` merges the inheritance values from `$codex`, as defined by the attribute inheritance keys (check `CodexServiceProvider::registerAttributeDefinitions()`)
// 3. `Project` merges the project's `config.php` value
// Returns the `Project`
$project  = $projects->get('project-key'); 



// Returns the `RevisionCollection`, dispatches `FindRevisions` once
$revisions = $project->getRevisions();

// Dispatches `ResolveRevision` once for every first unique get() call. `ResolveRevision`:
// 1. `Revision` receives its default attributes from `$codex['revisions']`
// 2. `Revision` merges the inheritance values from `$project`, as defined by the attribute inheritance keys 
// 3. `Revision` merges the revision's `revision.yml` value
// Returns the `Revision`
$revision  = $revisions->get('master');

// Returns the `DocumentCollection`, dispatches `FindDocuments` once
$documents = $revision->getDocuments();

// Dispatches `ResolveDocuments` once for every first unique get() call. 
// 1. `Document` sometimes receive attributes from the `AttributeProcessorExtension`
//    `AttributeProcessorExtension` checks the top part of the file content for (frontmatter) YAML attributes and merges it into the `Document`.   
// Returns the `Document`
$document  = $documents->get('index');

// Runs all processors
// returns the rendered value (HTML string) of the document
$content   = $document->render();

``` 


- The [CodexServiceProvider's](#codex:phpdoc['Codex\CodexServiceProvider']:popover:drawer) [booting()](#codex:phpdoc['Codex\CodexServiceProvider::booting()']:popover:drawer) function
    - Calls [registerAttributeDefinitions()](#codex:phpdoc['Codex\CodexServiceProvider::registerAttributeDefinitions()']:popover:drawer) to register the core attribute definitions/inheritance logic in their respective groups. 
        - group `codex` is largely populated by the `codex.php` configuration values.   
        - group `project` is populated using the `codex.projects` configuration value as default, .   
- [Codex](#codex:phpdoc['Codex\Codex']:popover:drawer) Loads configuration `codex`, dispatches [ProcessAttributes](#codex:phpdoc['Codex\Mergable\Commands\ProcessAttributes']:popover:drawer) 
- [Codex](#codex:phpdoc['Codex\Codex']:popover:drawer) instantiates [ProjectCollection](#codex:phpdoc['Codex\Projects\ProjectCollection']:popover:drawer) 
- [getProject($key)](#codex:phpdoc['Codex\Codex::getProject()']:popover:drawer) calls upon the [ProjectCollection::get($key)](#codex:phpdoc['Codex\Projects\ProjectCollection::get()']:popover:drawer) 
    - [ProjectCollection::get($key)](#codex:phpdoc['Codex\Projects\ProjectCollection::get()']:popover:drawer) dispatches [ResolveProject](#codex:phpdoc['Codex\Projects\Commands\ResolveProject']:popover:drawer)  


> A nice blockquote with a title
{: title="Blockquote title"}

{#id .class}
## Header


Codex is structured 
