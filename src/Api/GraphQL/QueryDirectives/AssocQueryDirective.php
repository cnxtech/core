<?php

namespace Codex\Api\GraphQL\QueryDirectives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\AST\PartialParser;

class AssocQueryDirective implements QueryDirective, QueryFieldManipulator
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
     * manipulateSchema method
     *
     * @param \GraphQL\Language\AST\FieldDefinitionNode      $fieldDefinition
     * @param \GraphQL\Language\AST\ObjectTypeDefinitionNode $parentType
     * @param \Nuwave\Lighthouse\Schema\AST\DocumentAST      $document
     *
     * @return \Nuwave\Lighthouse\Schema\AST\DocumentAST
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     * @throws \Nuwave\Lighthouse\Exceptions\ParseException
     */
    public function manipulateSchema(FieldDefinitionNode $fieldDefinition, ObjectTypeDefinitionNode $parentType, DocumentAST $document)
    {
        $newDefinition = $fieldDefinition->name->value . ': Assoc';
        if ($fieldDefinition->type instanceof NonNullTypeNode) {
            $newDefinition .= '!';
        }
        $partial            = PartialParser::fieldDefinition($newDefinition);
        $parentType->fields = ASTHelper::mergeUniqueNodeList($parentType->fields, [ $partial ], true);
        $document->setDefinition($parentType);
        return $document;
    }
}
