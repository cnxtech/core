<?php


namespace Codex\Config;


use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ConfigExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{

    /**
     * ConfigExpressionLanguageProvider constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions()
    {

        return [
//            new ExpressionFunction('codex', )
        ];
    }
}
