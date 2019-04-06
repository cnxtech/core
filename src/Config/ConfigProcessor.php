<?php


namespace Codex\Config;


use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ConfigProcessor
{
    /** @var string */
    protected $openTag = '{%';

    /** @var string */
    protected $closeTag = '%}';

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \Codex\Config\ExpressionLanguage
     */
    protected $expressionLanguage;

    /** @var array */
    protected $values = [];

    /** @var array  */
    protected $evaluatedExpressions = [];

    public function __construct(Application $app, ExpressionLanguage $expressionLanguage)
    {
        $this->app                = $app;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function hasTags(string $value)
    {
        $match = preg_match_all($this->getTagsPattern(), $value, $matches);
        return $match !== false && $match > 0;
    }

    public function getTagsPattern()
    {
        return sprintf("/%s([\w\W]*?)%s/m", $this->openTag, $this->closeTag);
    }

    public function process($value)
    {
        $this->evaluatedExpressions = [];
        return $this->processValue($value);
    }

    protected function processValue($value)
    {
        if ($this->isStringValue($value)) {
            $value = $this->processStringValue($value);
        }
        if ($this->isArrayValue($value)) {
            $value = $this->processArrayValue($value);
        }
        return $value;
    }

    protected function isArrayValue($value)
    {
        return is_array($value);
    }

    protected function processArrayValue(array $array)
    {
        foreach ($array as $key => $val) {
            if (is_string($val)) {
                $array[ $key ] = $this->processStringValue($val);
            } elseif (is_array($val)) {
                $array[ $key ] = $this->processArrayValue($val);
            }
        }

        return $array;
    }

    protected function isStringValue($value)
    {
        return is_string($value) && $this->hasTags($value);
    }

    protected function processStringValue($value)
    {
        if ( ! is_string($value) || ! $this->hasTags($value)) {
            return $value;
        }
        $match = preg_match_all($this->getTagsPattern(), $value, $matches);
        if ( ! $match) {
            return $value;
        }

        $originalValue = $value;
        $expressions   = last($matches);
        if (count($expressions) > 1) {

            foreach ($expressions as $key => $expression) {
                $evaluated = $this->evaluate($expression);
                $original  = $matches[ 0 ][ $key ];
                if (is_array($evaluated)) {
                    $evaluated = 'ERROR: array to string conversion :: ' . $originalValue;
                }
                $value = str_replace_first($original, $evaluated, $value);
            }
        } elseif (count($expressions) === 1) {
            $evaluated = $this->evaluate($expressions[ 0 ]);
            $original  = $matches[ 0 ][ 0 ];
            if (is_string($evaluated)) {
                $value = str_replace_first($original, $evaluated, $value);
            } else {
                $value = $evaluated;
            }
        }


        return $value;
    }

    public function evaluate($expression, array $vars = [])
    {
        $result=$expression;
        if (array_key_exists($expression, $this->evaluatedExpressions)) {
            return $this->evaluatedExpressions[ $expression ];
        }
        try {
            $result                                    = $this->expressionLanguage->evaluate($expression, array_replace($this->values, $vars));
            $result                                    = $this->process($result);
            $this->evaluatedExpressions[ $expression ] = $result;
            return $result;
        } catch(SyntaxError $syntaxError){
            return $result;
        }
    }

    public function setValue($key, $value, $overwrite = true)
    {
        data_set($this->values, $key, $value, $overwrite);
        return $this;
    }

    public function setValues(array $values)
    {
        $this->values = $values;
        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getExpressionLanguage()
    {
        return $this->expressionLanguage;
    }

    public function getOpenTag(): string
    {
        return $this->openTag;
    }

    public function getCloseTag(): string
    {
        return $this->closeTag;
    }

    public function setOpenTag(string $openTag): void
    {
        $this->openTag = $openTag;
    }

    public function setCloseTag(string $closeTag): void
    {
        $this->closeTag = $closeTag;
    }

    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage): void
    {
        $this->expressionLanguage = $expressionLanguage;
    }


}
