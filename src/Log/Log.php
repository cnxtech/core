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

use Codex\Contracts\Log\Log as LogContract;
use Illuminate\Console\Command;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as BaseLogger;

/**
 * This is the class Logger.
 *
 * @author         Codex Project
 * @copyright      Copyright (c) 2015, Codex Project. All rights reserved
 */
class Log extends BaseLogger implements LogContract
{
    protected $enabled;

    public function __construct()
    {
        parent::__construct('codex');
        $this->enabled = true;
    }

    public function useFiles($path, $level = self::DEBUG)
    {
        $formatter = tap(new LineFormatter(null, null, true, true), function ($formatter) {
            $formatter->includeStacktraces();
        });
        $handler   = new StreamHandler($path, $level);
        $this->pushHandler($handler->setFormatter($formatter));
        return $handler;
    }

    /**
     * Register a file log handler.
     *
     * @param string $path
     * @param string $level
     */
    public function useChromePHP($level = self::DEBUG)
    {
        $this->pushHandler($handler = new ChromePHPHandler($level));
        $handler->setFormatter($formatter = new ChromePHPFormatter());
        return $handler;
    }

    public function useFirePHP($level = self::DEBUG)
    {
        $this->pushHandler($handler = new FirePHPHandler($level));
        return $handler;
    }

    protected $artisanHandler;

    public function useArtisan(Command $command, $level = self::DEBUG)
    {
        if ($this->artisanHandler === null) {
            $this->pushHandler($this->artisanHandler = new ArtisanHandler($command, $level));
        }
        return $this->artisanHandler;
    }

    /**
     * Set the enabled value.
     *
     * @param mixed $enabled
     *
     * @return Log
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function addRecord($level, $message, array $context = [])
    {
        if ($this->enabled) {
            parent::addRecord($level, $message, $context);
        }
    }
}
