<?php

namespace Codex\Api\GraphQL\Directives;

class QueryConstraints
{
    protected $where = [];

    protected $orderBy = [];

    public function __construct(array $query = null)
    {
        $this->fromQuery($query);
    }

    public function fromQuery(array $query = null)
    {
        $query         = $query === null ? [] : $query;
        $this->where   = data_get($query, 'where', []);
        $this->orderBy = data_get($query, 'orderBy', []);
        return $this;
    }

    public function applyWhereConstraints($target, $methodName = 'where')
    {
        if (method_exists($target, $methodName)) {
            foreach ($this->where as $key => $value) {
                $column   = data_get($value, 'column');
                $operator = data_get($value, 'operator', '=');
                $value    = data_get($value, 'value');
                $boolean  = data_get($value, 'boolean', 'AND');
                $target   = $target->$methodName($column, $operator, $value);
            }
        }
        return $target;
    }

    public function applyOrderByConstraints($target, $methodName = 'orderBy')
    {
        if (method_exists($target, $methodName)) {
            foreach ($this->orderBy as $key => $value) {
                $column = data_get($value, 'column');
                $order  = data_get($value, 'order', 'DESC');
                $target = $target->$methodName($column, $order);
            }
        }
        return $target;
    }

    /**
     * applyConstraints method
     *
     * @param $target
     *
     */
    public function applyConstraints($target)
    {
        $target = $this->applyWhereConstraints($target);
        $target = $this->applyOrderByConstraints($target);
        return $target;
    }
}
