<?php

namespace Codex\Api\GraphQL\Scalars;

use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\ScalarType;

class Mixed extends ScalarType //Type
{
    public $name = 'Mixed';

    public function serialize($value)
    {
        return $value;
    }

    public function parseValue($value)
    {
        return $value;
    }

    public function parseLiteral($valueNode, array $variables = null)
    {
        $value = $this->getValueFromNode($valueNode);
        return $value;
    }

    protected function getValueFromNode($valueNode)
    {
        $valueNode = $valueNode->toArray(true);
        $kind      = data_get($valueNode, 'kind');
        if ($kind === NodeKind::OBJECT) {
            $names  = data_get($valueNode, 'fields.*.name.value');
            $values = data_get($valueNode, 'fields.*.value');
            $object = array_combine($names, $values);
            $object = collect($object)->map(function ($value) {
                return $this->castValueToKind($value[ 'value' ], $value[ 'kind' ]);
            })->toArray();
            return $object;
        }
        if ($kind === NodeKind::LST) {
            $values = data_get($valueNode, 'values.*');
            $values = collect($values)->map(function ($value) {
                return $this->castValueToKind($value[ 'value' ], $value[ 'kind' ]);
            })->toArray();
            return $values;
        }
        if (isset($valueNode[ 'value' ])) {
            return $this->castValueToKind($valueNode[ 'value' ], $kind);
        }
        return null;
    }

    protected function castValueToKind($value, $kind)
    {
        switch ($kind) {
            case NodeKind::STRING:
                return (string)$value;
            case NodeKind::INT:
                return (int)$value;
            case NodeKind::BOOLEAN:
                return (bool)$value;
            case NodeKind::FLOAT:
                return (float)$value;
            case NodeKind::ENUM:
                return $value;
            case NodeKind::NULL:
                return $value;
            case NodeKind::LST:
                return $value;
            case NodeKind::OBJECT:
                return $value;
            default:
                return $value;
        }
    }
}
