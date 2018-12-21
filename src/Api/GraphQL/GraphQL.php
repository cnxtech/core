<?php

namespace Codex\Api\GraphQL;

use Codex\Api\GraphQL\QueryDirectives\QueryFieldManipulator;
use GraphQL\Error\Error;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Visitor;
use GraphQL\Validator\DocumentValidator;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\PartialParser;
use Nuwave\Lighthouse\Schema\Extensions\ExtensionRegistry;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Support\Pipeline;

class GraphQL extends \Nuwave\Lighthouse\GraphQL
{

    /** @var \Codex\Api\GraphQL\QueryDirectiveRegistry */
    protected $queryDirectiveRegistry;

    public function __construct(
        ExtensionRegistry $extensionRegistry,
        SchemaBuilder $schemaBuilder,
        SchemaSourceProvider $schemaSourceProvider,
        Pipeline $pipeline,
        QueryDirectiveRegistry $queryDirectiveRegistry
    )
    {
        parent::__construct($extensionRegistry, $schemaBuilder, $schemaSourceProvider, $pipeline);
        $this->queryDirectiveRegistry = $queryDirectiveRegistry;
    }


    protected function prepSchemaWithQuery(string $query)
    {
        if (empty($this->executableSchema)) {
            $document = $this->documentAST();
            $parsed   = Parser::parse($query);

            Visitor::visit($parsed, [
                'Directive' => [
                    'leave' => function ($node, $key, $parent, $path, $ancestors) use ($document) {
                        if ( ! $this->queryDirectiveRegistry->has($node->name->value)) {
                            return null;
                        }
                        $directive = $this->queryDirectiveRegistry->get($node->name->value);

                        $parentType = $document->objectTypeDefinition($ancestors[ 0 ]->kind);
                        if ( ! $parentType) {
                            throw new \Exception('parent type not found');
                        }

                        /** @var \Illuminate\Support\Collection|FieldDefinitionNode[] $fields */
                        $fields = collect($parentType->fields)->keyBy(function (FieldDefinitionNode $fieldDefinitionNode) {
                            return $fieldDefinitionNode->name->value;
                        });

                        /** @var \Illuminate\Support\Collection|\GraphQL\Language\AST\FieldNode[] $ancestorFieldNodes */
                        $ancestorFieldNodes = collect($ancestors)->filter(function ($ancestor) {
                            return $ancestor instanceof FieldNode;
                        })->values();

                        $field = $fields[ $ancestorFieldNodes[ 1 ]->name->value ];

                        if ($directive instanceof QueryFieldManipulator) {
                            $directive->manipulateSchema($field, $parentType, $document);
                        }



                        /** @var FieldDefinitionNode $fieldDef */
//                    $fieldDef         = Utils::find($document->queryTypeDefinition()->fields, function (FieldDefinitionNode $value) use ($fields) {
//                        return $value->name->value === $fields[ 0 ]->name->value;
//                    });
//                    $fieldDefTypeName = ASTHelper::getFieldTypeName($fieldDef);
//                    $parentType     = $document->objectTypeDefinition($node->kind);
//
//
//                    /** @var \GraphQL\Language\AST\FieldNode $field */
//                    $field = last($ancestors);
//                    $document->objectTypeDefinitions();
//                    $queryDef = $document->queryTypeDefinition()->fields;
//                    $fieldDef = $document->queryTypeDefinition()->fields[ 0 ];
////                    $fieldDef = $schema->getQueryType()->getField($fields[ 0 ]->name->value);
//                    $type = $fieldDef->type;
//                    if ($type instanceof WrappingType) {
//                        $wrapped     = $type->getWrappedType(true);
//                        $wrappedType = $schema->getType($wrapped->name);
//                        $subField    = $wrapped->getField($fields[ 1 ]->name->value);
//
//                        $partial      = PartialParser::fieldDefinition('layout: Assoc!');
//                        $revisionType = $schema->getType('Revision');
//                        $assocType    = $schema->getType('Assoc');
//                    }
                        // return
                        //   null: no action
                        //   Visitor::stop(): stop visiting altogether
                        //   Visitor::removeNode(): delete this node
                        //   any value: replace this node with the returned value
                        return null;
                    },
                ],
            ]);
            $this->executableSchema = $this->schemaBuilder->build(
                $document
            );
        }

        return $this->executableSchema;
    }

    public function executeQuery(string $query, $context = null, $variables = [], $rootValue = null): ExecutionResult
    {
        $result = \GraphQL\GraphQL::executeQuery(
            $this->prepSchemaWithQuery($query),
            $query,
            $rootValue,
            $context,
            $variables,
            app('request')->input('operationName'),
            null,
            $this->getValidationRules() + DocumentValidator::defaultRules()
        );

        $result->extensions = $this->extensionRegistry->jsonSerialize();

        $result->setErrorsHandler(
            function (array $errors, callable $formatter): array {
                // Do report: Errors that are not client safe, schema definition errors
                // Do not report: Validation, Errors that are meant for the final user
                // Misformed Queries: Log if you are dog-fooding your app

                /**
                 * Handlers are defined as classes in the config.
                 * They must implement the Interface \Nuwave\Lighthouse\Execution\ErrorHandler
                 * This allows the user to register multiple handlers and pipe the errors through.
                 */
                $handlers = config('lighthouse.error_handlers', []);

                return array_map(
                    function (Error $error) use ($handlers, $formatter) {
                        return $this->pipeline
                            ->send($error)
                            ->through($handlers)
                            ->then(function (Error $error) use ($formatter) {
                                return $formatter($error);
                            });
                    },
                    $errors
                );
            }
        );

        return $result;
    }


}
