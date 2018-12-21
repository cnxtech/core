<?php

namespace Codex\Tests\Feature\Mergable;

use Codex\Codex;
use Codex\Tests\Feature\FeatureTestCase;

class MergeRevisionTest extends FeatureTestCase
{
    public function testParentMerge()
    {
        $codex = $this->app->make(Codex::class);
        $this->assertFalse($codex->getAttribute('processors.toc.header_link_show'));
        $project = $codex->getProject('codex');
        $this->assertTrue($project->getAttribute('processors.toc.header_link_show'));
        $a = 'a';
    }
}
