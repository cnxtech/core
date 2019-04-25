<?php

namespace Codex\Tests\Unit\Attr;

use Codex\Attributes\AttributeDefinition as Def;

class DefinitionTest extends \Codex\Tests\TestCase
{

    public function testSetName()
    {
        $this->assertEquals('main', with(new Def)->name('main')->get('name'));
    }

}
