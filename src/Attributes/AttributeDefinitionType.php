<?php

namespace Codex\Attributes;

use MyCLabs\Enum\Enum;

/**
 * @method static AttributeDefinitionType MIXED()
 * @method static AttributeDefinitionType DICTIONARY()
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
    const OBJECT = 'object';
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const ARRAY = 'array';

    public static $apiTypeMap = [
        self::MIXED      => 'Mixed',
        self::DICTIONARY => 'Assoc',
        self::OBJECT     => 'Mixed',
        self::STRING     => 'String',
        self::BOOLEAN    => 'Boolean',
        self::INTEGER    => 'Int',
        self::ARRAY      => 'Assoc',
    ];

    public function toApiType(): string
    {
        return static::$apiTypeMap[ $this->value ];
    }

    public static $nodeTypeMap = [
        self::MIXED      => 'scalar',
        self::DICTIONARY => 'array',
        self::OBJECT     => 'scalar',
        self::STRING     => 'scalar',
        self::BOOLEAN    => 'boolean',
        self::INTEGER    => 'integer',
        self::ARRAY      => 'array',
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
