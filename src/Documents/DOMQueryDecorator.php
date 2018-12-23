<?php

namespace Codex\Documents;

/**
 * This is the class DOMQueryDecorator.
 *
 * @package Codex\Documents
 * @author  Robin Radic
 * @mixin \FluentDOM\Query
 */
class DOMQueryDecorator implements \ArrayAccess
{
    /** @var \Codex\Contracts\Documents\Document */
    protected $_codexDocument;

    /** @var \FluentDOM\Query */
    private $query;

    public function __construct(\FluentDOM\Query $query, \Codex\Contracts\Documents\Document $document)
    {
        $this->query          = $query;
        $this->_codexDocument = $document;
    }

    public function saveToDocument()
    {
        $this->_codexDocument->saveDOM($this->query);
        return $this;
    }

    public function querySelector(string $selector)
    {
        return $this->query->document->querySelector($selector);
    }

    public function querySelectorAll(string $selector)
    {
        return $this->query->document->querySelectorAll($selector);
    }

    /** @return \FluentDOM\DOM\Element[]|\Illuminate\Support\Collection */
    public function query(string $selector)
    {
        $els = [];
        foreach ($this->querySelectorAll($selector) as $el) {
            $els[] = $el;
        }
        return collect($els);
    }

    //region: Magic methods

    public function __toString(): string
    {
        return $this->query->__toString();
    }

    public function __isset(string $name): bool
    {
        return $this->query->__isset($name);
    }

    public function __get(string $name)
    {
        return $this->query->__get($name);
    }

    public function __set(string $name, $value)
    {
        $this->query->__set($name, $value);
    }

    public function __unset(string $name)
    {
        $this->query->__unset($name);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([ $this->query, $name ], $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return forward_static_call_array([ static::class, $name ], $arguments);
    }

    public function offsetExists($offset)
    {
        return $this->query->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->query->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->query->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->query->offsetUnset($offset);
    }

    //endregion
}
