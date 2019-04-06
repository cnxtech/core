<?php


namespace Codex\Config;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;


class ExpressionLanguage extends BaseExpressionLanguage
{

    public function __construct(CacheItemPoolInterface $parser = null, array $providers = [])
    {
        parent::__construct($parser, $providers);
    }
}
