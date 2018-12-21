<?php

namespace Codex\Api\GraphQL\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Context;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective as LighthouseBaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Exceptions\DirectiveException;

class FilterDirective extends LighthouseBaseDirective implements FieldMiddleware
{
    static public $defaultResolver = self::class . '@resolveFilter';

    public function name()
    {
        return 'filter';
    }

    public function resolveFilter($root, array $args, Context $context = null, ResolveInfo $info = null)
    {
        $a = func_get_args();
        return [];
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
//        $eventBaseName = $this->directiveArgValue('fire') ?? $this->directiveArgValue('class');
//        $eventClassName = $this->namespaceClassName($eventBaseName);
        $valueResolver = $value->getResolver();

        return $next($value->setResolver(function (...$args) use ($valueResolver) {
            $value          = $valueResolver(...$args);
            $filterResolver = null;
            try {
                $filterResolver = $this->getResolver();
            }
            catch (DirectiveException $e) {
                list($class, $method) = explode('@', static::$defaultResolver);
                $filterResolver = \Closure::fromCallable([ app($class), $method ]);
            }
            $value  = $filterResolver($value);

            return $resolved;
        }));
    }

}


