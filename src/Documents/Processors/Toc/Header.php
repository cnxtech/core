<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

namespace Codex\Documents\Processors\Toc;

use Illuminate\Contracts\Support\Arrayable;
use Tree\Node\Node;

/**
 * This is the class Header.
 *
 * @author         CLI
 * @copyright      Copyright (c) 2015, CLI. All rights reserved
 *
 * @method HeaderValue getValue()
 */
class Header extends Node implements Arrayable
{
    public $size;

    public $text;

    public static function make($size, $text)
    {
        $value = new HeaderValue($size, $text);
        $node = new static($value);
        $node->size = $size;
        $node->text = $text;
        $value->setHeader($node);

        return $node;
    }

    public function toArray()
    {
        $data = [
            'size' => $this->size,
            'text' => $this->text,
            'value' => $this->getValue(),
        ];
        $children = [];
        foreach ($this->getChildren() as $child) {
            if ($child instanceof Arrayable) {
                $children[] = $child->toArray();
            }
        }
        if (\count($children) > 0) {
            $data['children'] = $children;
        }

        return $data;
    }

    public function hasChildren()
    {
        return \count($this->getChildren()) > 0;
    }
}
