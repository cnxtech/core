<?php

namespace Codex\Api\GraphQL\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class AssocDirective extends BaseDirective implements FieldResolver,FieldManipulator,FieldMiddleware
{

    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name()
    {
        return 'assoc';
    }

    /**
     * Set a field resolver on the FieldValue.
     *
     * This must call $fieldValue->setResolver() before returning
     * the FieldValue.
     *
     * @param FieldValue $fieldValue
     *
     * @return FieldValue
     */
    public function resolveField(FieldValue $fieldValue)
    {
        $resolver = $fieldValue->getResolver();
        $fieldValue->setResolver(function ($rootValue, array $args, $context, ResolveInfo $info) use  ($resolver){
            $value = $resolver($rootValue,$args,$context,$info);

            return $value;
        });
        return $fieldValue;
    }

    /**
     * @param FieldDefinitionNode      $fieldDefinition
     * @param ObjectTypeDefinitionNode $parentType
     * @param DocumentAST              $current
     *
     * @return DocumentAST
     */
    public function manipulateSchema(FieldDefinitionNode $fieldDefinition,
                                     ObjectTypeDefinitionNode $parentType,
                                     DocumentAST $current)
    {
        return $current;
    }

    /**
     * Resolve the field directive.
     *
     * @param FieldValue $value
     * @param \Closure   $next
     *
     * @return FieldValue
     */
    public function handleField(FieldValue $value, \Closure $next)
    {
        return $value;
    }
}
