<?php

namespace Codex\Tests\Feature\Api\Queries;

use Codex\Tests\Feature\Api\ApiTestCase;

class ProjectsQueryTest extends ApiTestCase
{

    public function testProjectsQuery()
    {
        $result   = $this->executeQuery('{
            projects {
                key                
                description
                display_name
                document {
                    cache {
                        minutes
                        mode
                    }
                    default
                    extensions
                    view
                }
                revision {
                    allow_php_config
                    allowed_config_files
                    default
                }
            }
        }');
        $projects = array_map(function ($key) {
            $project = codex()->getProject($key);
            return $project->getGraphSelection([ 'key', 'description', 'display_name', 'document', 'revision' ]);
        }, codex()->getProjects()->keys());
        $this->assertEquals(compact('projects'), $result->data);
    }

    public function testProjectsQueryWhereConstraints()
    {
        $variables = [
            'constraints' => [
                'where' => [ [ 'column' => 'key', 'operator' => '=', 'value' => 'blade-extensions', ], ],
            ],
        ];
        $result    = $this->executeQuery('query TestProjectsQueryConstraints($constraints:Assoc!){
            projects(query : $constraints) {
                key                
            }
        }', $variables);

        $projects = [
            [ 'key' => 'blade-extensions' ],
        ];
        $diff     = array_diff(array_dot(compact('projects')), array_dot($result->data));
        $this->assertEmpty($diff);
    }

    protected function executeOrderConstraintQuery($order)
    {
        $variables = [
            'constraints' => [
                'orderBy' => [
                    [ 'column' => 'key', 'order' => $order ],
                ],
            ],
        ];
        return $this->executeQuery('query TestProjectsQueryConstraints($constraints:Assoc!){
            projects(query : $constraints) {
                key                
            }
        }', $variables);
    }

    public function testOrderConstraintsQueryASC()
    {
        $result   = $this->executeOrderConstraintQuery('ASC');
        $projects = [
            [ 'key' => 'blade-extensions' ],
            [ 'key' => 'codex' ],
        ];
        $this->assertEquals(compact('projects'), $result->data);
    }

    public function testOrderConstraintsQueryDESC()
    {
        $result   = $this->executeOrderConstraintQuery('DESC');
        $projects = [
            [ 'key' => 'codex' ],
            [ 'key' => 'blade-extensions' ],
        ];
        $this->assertEquals(compact('projects'), $result->data);
    }


}
