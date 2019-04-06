<?php

namespace Codex\Commands;

use Codex\Filesystem\Tmp;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\View\Compilers\BladeCompiler;

class CompileBladeString
{
    /** @var string */
    protected $string;

    /** @var array */
    protected $vars;

    public function __construct(string $string, array $vars = [])
    {
        $this->string = $string;
        $this->vars   = $vars;
    }

    public function handle(BladeCompiler $compiler)
    {
        try {
            $tmp        = new Tmp('blade-string');
            $fileSuffix = uniqid(time(), true);
            $string     = $compiler->compileString((string)$this->string);
            $file       = $tmp->createTmpFile($fileSuffix);
            $filePath   = $file->getRealPath();

            if (false === file_put_contents($filePath, $string)) {
                return $this->string;
            }

            $compile = function (string $__path) {
                if (is_array($this->vars) && ! empty($this->vars)) {
                    extract($this->vars, EXTR_SKIP);
                }
                ob_start();
                include $__path;
                return (string)ob_get_clean();
            };

            if (is_file($filePath)) {
                $string = $compile($filePath);
                unlink($filePath);
            }

            return $string;
        }
        catch (\Throwable $e) {
            return $this->string;
        }
    }
}
