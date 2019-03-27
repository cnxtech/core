<?php
/**
 * Copyright (c) 2018. Codex Project.
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author    Robin Radic
 * @license   https://codex-project.mit-license.org MIT License
 */

namespace Codex\Documents\Processors\Macros;

use Closure;
use Codex\Exceptions\Exception;
use Codex\Exceptions\InvalidArgumentException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * The Macro class represents.
 *
 * @author         CLI
 * @copyright      Copyright (c) 2015, CLI. All rights reserved
 */
class Macro
{
    /** @var \Codex\Documents\Document */
    public $document;

    /** @var \Codex\Projects\Project */
    public $project;

    /** @var \Codex\Codex */
    public $codex;

    /**
     * The Class[at]method call signature for the class method that should be called. As configured.
     *
     * @var
     */
    public $handler;

    /** @var array */
    public $arguments = [];

    /**
     * The cleaned macro string (eg: jira:issues:search('project="CODEX"', 54) ).
     *
     * @var string
     */
    public $cleaned;

    /**
     * The raw macro string (eg: <!--*codex:jira:issues:search('project="CODEX"', 54)*--> or <!--*codex:general:hide*--> or <!--*codex:/general:hide*-->.
     *
     * @var string
     */
    public $raw;

    /**
     * The definition is how the macro key. Similair to how it is registered in the config (eg: 'jira:issues:search' or 'general:hide' or 'table:responsive').
     *
     * @var string
     */
    public $definition;

    /**
     * DocTag constructor.
     *
     * @param string $raw
     * @param string $cleaned
     */
    public function __construct($raw, $cleaned)
    {
        $this->raw        = $raw;
        $this->cleaned    = $cleaned;
        $this->definition = static::extractDefinition($cleaned);
    }

    /**
     * The definition is how the macro key. Similair to how it is registered in the config (eg: 'jira:issues:search' or 'general:hide' or 'table:responsive').
     *
     * @param string $cleaned
     *
     * @return string
     *
     */
    public static function extractDefinition($cleaned)
    {
        if (0 === preg_match_all('/(?:\/|^)(.*?)(?:\(|$)/', $cleaned, $definition)) {
            throw Exception::make('Macro definition could not be extracted');
        }

        return $definition[ 1 ][ 0 ];
    }

    public function isClosing()
    {
        return starts_with($this->cleaned, '/');
    }

    public function hasArguments()
    {
        return str_contains($this->cleaned, [ '(', ')' ]);
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    protected function parseArguments()
    {
        $this->arguments = [];
        //https://regex101.com/r/gB9bP9/2
        if (preg_match('/\((.*?)\)(?!.*\))/', $this->cleaned, $argumentString) < 1) {
            return;
        }
        $argumentString  = last($argumentString);
        $argumentString  = preg_replace('/(?<!\\\)\\\(?!\\\)|(?<!\\\)\\\\\\\(?!\\\)/', '\\\\\\', $argumentString);
        $this->arguments = json5_decode('[' . $argumentString . ']', true, 512);
    }

    protected function getCallable()
    {
        if ($this->handler instanceof Closure) {
            return $this->handler;
        } else {
            // assuming its a @ string
            list($class, $method) = explode('@', (string)$this->handler);
            $instance = app()->make($class);
            foreach ([ 'codex', 'document', 'project', 'definition' ] as $property) {
                if (property_exists($instance, $property)) {
                    $instance->{$property} = $this->{$property};
                }
            }

            return [ $instance, $method ];
        }
    }

    public function canRun()
    {
        return $this->raw && $this->cleaned && $this->handler;
    }

    public function run()
    {
        if ($this->canRun()) {
//            $content = $this->document->getContent();
            try {
                $this->parseArguments();
                $arguments = array_merge([ $this->isClosing() ], $this->arguments);
                $result    = \call_user_func_array($this->getCallable(), $arguments);
                return $result;
            }
            catch (\TypeError $exception) {
                throw InvalidArgumentException::from($exception);
            }
//            $content = preg_replace('/'.preg_quote($this->raw, '/').'/', $result, $content, 1);
//            $this->document->setContent($content);
        } else {
            throw Exception::make("Macro [{$this->cleaned}] cannot call because some properties havent been set. Prevent the Macro from running by using the canRun() method.");
        }
    }
}
