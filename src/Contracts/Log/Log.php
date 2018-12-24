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

namespace Codex\Contracts\Log;

use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

/**
 * Interface Log.
 *
 * @author  Robin Radic
 * @mixin \Codex\Log\Log
 */
interface Log extends LoggerInterface
{
    public function useFiles($path, $level = self::DEBUG);

    public function useChromePHP($level = self::DEBUG);

    public function useFirePHP($level = self::DEBUG);

    public function useArtisan(Command $command, $level = self::DEBUG);

    public function setEnabled($enabled);

}
