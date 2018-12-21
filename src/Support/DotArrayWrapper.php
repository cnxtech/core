<?php

namespace Codex\Support;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use IteratorAggregate;


class DotArrayWrapper implements Arrayable, ArrayAccess, IteratorAggregate, Countable
{
    use Macroable;
    use HasDotArray;
}
