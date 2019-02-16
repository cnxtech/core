<?php

namespace Codex\Documents\Processors\Parser\CommonMark;

use League\CommonMark\Extension\Extension;

class CodexCommonMarkExtension extends Extension
{

    public function getBlockRenderers()
    {
        return [
            'League\CommonMark\Block\Element\FencedCode' => new FencedCodeRenderer(),
        ];
    }

}
