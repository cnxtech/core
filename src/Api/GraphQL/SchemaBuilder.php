<?php

namespace Codex\Api\GraphQL;

use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;

class SchemaBuilder extends \Nuwave\Lighthouse\Schema\SchemaBuilder
{
    public function build($documentAST)
    {
        foreach ($documentAST->typeDefinitions() as $typeDefinition) {
            $type = $this->nodeFactory->handle($typeDefinition);
            $this->typeRegistry->register($type);

            switch ($type->name) {
                case 'Query':
                    /** @var ObjectType $queryType */
                    $queryType = $type;
                    continue 2;
                case 'Mutation':
                    /** @var ObjectType $mutationType */
                    $mutationType = $type;
                    continue 2;
                case 'Subscription':
                    /** @var ObjectType $subscriptionType */
                    $subscriptionType = $type;
                    continue 2;
                default:
                    $types [] = $type;
            }
        }

        if (empty($queryType)) {
            throw new InvariantViolation("The root Query type must be present in the schema.");
        }

        $config = SchemaConfig::create()
            // Always set Query since it is required
            ->setQuery(
                $queryType
            )
            // Not using lazy loading, as we do not have a way of discovering
            // orphaned types at the moment
            ->setTypes(
                $types
            )
            ->setDirectives(
                $this->convertDirectives($documentAST)
                    ->toArray()
            );

        // Those are optional so only add them if they are present in the schema
        if (isset($mutationType)) {
            $config->setMutation($mutationType);
        }
        if (isset($subscriptionType)) {
            $config->setSubscription($subscriptionType);
        }

        return new Schema($config);
    }

}
