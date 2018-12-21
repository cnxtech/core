<?php

namespace Codex\Api\GraphQL\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\AST\PartialParser;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class ConstraintsDirective extends BaseDirective implements FieldManipulator, FieldResolver
{
    static public $defaultResolver = self::class . '@resolveConstraints';

    public function name()
    {
        return 'constraints';
    }

    /**
     * resolveConstraints method
     *
     * @param \Illuminate\Support\Collection|\Codex\Mergable\EloquentCollection|array $collection
     * @param                                                                         $query
     *
     * @return array
     */
    public function resolveConstraints(QueryConstraints $constraints)
    {
        return [];
    }

    /**
     * manipulateSchema method
     *
     * @param \GraphQL\Language\AST\FieldDefinitionNode      $fieldDefinition
     * @param \GraphQL\Language\AST\ObjectTypeDefinitionNode $parentType
     * @param \Nuwave\Lighthouse\Schema\AST\DocumentAST      $current
     * @param \Nuwave\Lighthouse\Schema\AST\DocumentAST      $original
     *
     * @return \Nuwave\Lighthouse\Schema\AST\DocumentAST
     * @throws \Nuwave\Lighthouse\Support\Exceptions\DocumentASTException
     */
    public function manipulateSchema(FieldDefinitionNode $fieldDefinition, ObjectTypeDefinitionNode $parentType, DocumentAST $current)
    {
        $connectionArguments                           = PartialParser::inputValueDefinitions([
            'query: QueryConstraints',
            'page: Int',
            'count: Int',
        ]);

        $fieldDefinition->arguments = ASTHelper::mergeNodeList($fieldDefinition->arguments, $connectionArguments);
        $parentType->fields         = ASTHelper::mergeNodeList($parentType->fields, [ $fieldDefinition ]);
        $current->setDefinition($parentType);
        return $current;
    }

    public function resolveField(FieldValue $value)
    {
        $directiveResolver = null;
        try {
            $directiveResolver = $this->getResolverFromArgument('resolver');
        }
        catch (DirectiveException $e) {
            list($class, $method) = explode('@', static::$defaultResolver);
            $directiveResolver = \Closure::fromCallable([ app($class), $method ]);
        }

        return $value->setResolver(function ($root, array $args, $context = null, ResolveInfo $info = null) use ($directiveResolver) {
            $query = data_get($args, 'query', null);
            $page  = $this->argument('page', 1);
            $count = $this->argument('count', 15);

            $constraints = $this->makeQueryConstraints($query);

            $resolverReflection = new \ReflectionFunction($directiveResolver);
            list($className, $methodName) = array_values($resolverReflection->getStaticVariables());
            $instance = app()->make($className);
            $value = app()->call([$instance,$methodName], compact('constraints', 'query', 'root', 'args', 'context', 'info'));
            return $value;
        });
    }

    protected function makeQueryConstraints($query)
    {
        return with(new QueryConstraints)->fromQuery($query);
    }
}


