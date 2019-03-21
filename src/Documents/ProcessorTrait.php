<?php


namespace Codex\Documents;


use Codex\Hooks;

trait ProcessorTrait
{

    /** @var bool */
    protected $preProcessed = false;

    /** @var bool */
    protected $processed = false;

    /** @var bool */
    protected $postProcessed = false;

    public function process()
    {
        if ( ! $this->isProcessed()) {
            Hooks::run($this->getProcessHookPrefix() . 'process', [ $this ]);
            $this->setProcessed(true);
        }
    }

    public function preprocess()
    {
        if ( ! $this->isPreProcessed()) {
            Hooks::run($this->getProcessHookPrefix() . 'preProcess', [ $this ]);
            $this->setPreProcessed(true);
        }
    }

    public function postprocess()
    {
        if ( ! $this->isPostProcessed()) {
            Hooks::run($this->getProcessHookPrefix() . 'postProcess', [ $this ]);
            $this->setPostProcessed(true);
        }
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed($processed)
    {
        $this->processed = $processed;
        return $this;
    }

    public function isPreProcessed(): bool
    {
        return $this->preProcessed;
    }

    public function setPreProcessed($preProcessed)
    {
        $this->preProcessed = $preProcessed;
        return $this;
    }

    public function isPostProcessed(): bool
    {
        return $this->postProcessed;
    }

    public function setPostProcessed($postProcessed)
    {
        $this->postProcessed = $postProcessed;
        return $this;
    }

    abstract function getProcessHookPrefix(): string;
}
