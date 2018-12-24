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

namespace Codex\Log;

use Codex\Contracts\Log\Log;
use Illuminate\Console\Command;
use Illuminate\Log\Logger as BaseLogger;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;

/**
 * This is the class Logger.
 *
 * @author         Codex Project
 * @copyright      Copyright (c) 2015, Codex Project. All rights reserved
 */
class Logger extends BaseLogger implements Log
{
    /** @var \Monolog\Logger */
    protected $logger;
    protected $enabled;

    public function __construct(\Monolog\Logger $monolog, \Illuminate\Contracts\Events\Dispatcher $dispatcher)
    {
        parent::__construct($monolog, $dispatcher);
        $this->enabled = true;
    }

    public function useCodex($path, $level = 'debug')
    {
        $this->useFiles($path, $level);
        //$this->useChromePHP($level);
        $this->useFirePHP($level);
    }

    /**
     * Register a file log handler.
     *
     * @param string $path
     * @param string $level
     */
    public function useChromePHP($level = 'debug')
    {
        $this->logger->pushHandler($handler = new ChromePHPHandler($this->parseLevel($level)));
        $handler->setFormatter($formatter = new ChromePHPFormatter());
    }

    public function useFirePHP($level = 'debug')
    {
        $this->logger->pushHandler($handler = new FirePHPHandler($this->parseLevel($level)));
    }

    public function useArtisan($level = 'debug', Command $command)
    {
        $this->logger->pushHandler($handler = new ArtisanHandler($this->parseLevel($level), $command));
    }

    protected function writeLog($level, $message, $context)
    {
        if ($this->enabled) {
            parent::writeLog($level, $message, $context);
        }
    }

    /**
     * Set the enabled value.
     *
     * @param mixed $enabled
     *
     * @return Logger
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }
}
