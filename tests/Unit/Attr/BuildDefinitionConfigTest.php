<?php


namespace Codex\Tests\Unit\Attr;


use Codex\Attributes\Commands\BuildDefinitionConfig;
use Codex\Attributes\AttributeDefinition;
use Codex\Attributes\AttributeDefinitionRegistry;
use Codex\Tests\TestCase;

class BuildDefinitionConfigTest extends TestCase
{
    protected function build(AttributeDefinition $group)
    {
        $command = new BuildDefinitionConfig($group);
        return $command->handle();
    }

    protected function create($groupName)
    {
        $registry = new AttributeDefinitionRegistry();
        return $registry->has($groupName) ? $registry->get($groupName) : $registry->add($groupName)->get($groupName);
    }


    public function testSimple()
    {
        $group = $this->create('test');
        $group->child('name', 'string', 'default');
        $node = $this->build($group);
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals([ 'name' => 'default' ], $node->finalize([]));
        $this->assertEquals([ 'name' => 'value' ], $node->finalize([ 'name' => 'value' ]));
    }
}
