<?php

namespace Codex\Documents\Processors\Macros;

class Components
{


    public function gist($isCloser = false, $gist = '', $file = null)
    {
        return "<c-gist gist='{$gist}' file='{$file}'></c-gist>";
    }

    public function scrollbar($isCloser = false, $maxHeight=100, $options=[])
    {
        $props = [
            'autoHeight'    => true,
            'autoHeightMax' => (int) $maxHeight,
        ];
        $props = array_replace($props, $options);
        $props = json_encode($props, JSON_UNESCAPED_SLASHES);
        return $isCloser ? "</c-scrollbar>" : "<c-scrollbar props='{$props}'>";
    }
}
