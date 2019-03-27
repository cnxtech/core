<?php

namespace Codex\Attributes;

use MyCLabs\Enum\Enum;

/**
 * @method static AttributeDefinitionType MIXED()
 * @method static AttributeDefinitionType DICTIONARY()
 * @method static AttributeDefinitionType DICTIONARY_ARRAY()
 * @method static AttributeDefinitionType ARRAY_ARRAY()
 * @method static AttributeDefinitionType ARRAY_SCALAR()
 * @method static AttributeDefinitionType ARRAY_RECURSIVE()
 * @method static AttributeDefinitionType RECURSE()
 * @method static AttributeDefinitionType OBJECT()
 * @method static AttributeDefinitionType STRING()
 * @method static AttributeDefinitionType BOOLEAN()
 * @method static AttributeDefinitionType INTEGER()
 * @method static AttributeDefinitionType ARRAY()
 */
class AttributeDefinitionType extends Enum
{
    const MIXED = 'mixed';
    const DICTIONARY = 'dictionary';
    const DICTIONARY_ARRAY = 'dictionaryPrototype';
    const ARRAY_DICTIONARY = 'array.dictionaryPrototype';
    const ARRAY_ARRAY = 'array.arrayPrototype';
    const ARRAY_SCALAR = 'array.scalarPrototype';
    const ARRAY_RECURSIVE = 'array.recursive';
    const RECURSE = 'recurse';
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const ARRAY = 'array';

    public static $apiTypeMap = [
        self::MIXED            => 'Mixed',
        self::DICTIONARY       => 'Assoc',
        self::DICTIONARY_ARRAY => '[Assoc]',
        self::ARRAY_DICTIONARY => 'Assoc',
        self::ARRAY_ARRAY      => 'Assoc',
        self::ARRAY_SCALAR     => 'Assoc',
        self::ARRAY_RECURSIVE  => 'Assoc',
        self::RECURSE          => 'Assoc',
        self::STRING           => 'String',
        self::BOOLEAN          => 'Boolean',
        self::INTEGER          => 'Int',
        self::ARRAY            => 'Assoc',
    ];

    public function toApiType(): string
    {
        return static::$apiTypeMap[ $this->value ];
    }

    public static $nodeTypeMap = [
        self::MIXED            => 'scalar',
        self::DICTIONARY       => 'dictionary',
        self::DICTIONARY_ARRAY => 'dictionaryPrototype',
        self::ARRAY_DICTIONARY => 'array.dictionaryPrototype',
        self::ARRAY_ARRAY      => 'array.arrayPrototype',
        self::ARRAY_SCALAR     => 'array.scalarPrototype',
        self::ARRAY_RECURSIVE  => 'array.recursive',
        self::RECURSE          => 'recurse',
        self::STRING           => 'scalar',
        self::BOOLEAN          => 'boolean',
        self::INTEGER          => 'integer',
        self::ARRAY            => 'array',
    ];

    public function toNodeType(): string
    {
        return static::$nodeTypeMap[ $this->value ];
    }

    /**
     * Checks if the given types match the instance type
     *
     * @param Enum[] ...$types
     *
     * @return bool
     */
    public function is(...$types)
    {
        foreach ($types as $type) {
            if ($this->equals($type)) {
                return true;
            }
        }
        return false;
    }
}
