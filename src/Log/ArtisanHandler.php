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

use Illuminate\Console\Command;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class ArtisanHandler extends AbstractHandler
{
    /** @var \Illuminate\Console\Command */
    protected $command;

    public function __construct($level = Logger::DEBUG, Command $command, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->command = $command;
    }

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param array $record The record to handle
     *
     * @return bool true means that this handler handled the record, and that bubbling is not permitted.
     *              false means the record was either not processed or that this handler allows bubbling
     */
    public function handle(array $record)
    {
        $channel = $record['channel'];
        $message = "{$record['level_name']} :: {$record['message']}";
        if ('error' === $channel) {
            $this->command->error($message);
        } elseif ('alert' === $channel) {
            $this->command->alert($message);
        } elseif ('warning' === $channel) {
            $this->command->warn($message);
        } else {
            $this->command->line($message);
        }
        // TODO: Implement handle() method.
    }
}
