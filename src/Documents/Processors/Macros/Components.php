<?php

namespace Codex\Documents\Processors\Macros;

class Components
{

    public function gist($isCloser = false, $gist = '', $file = null)
    {
        return "<c-gist props='{$this->props(compact('gist','file'))}'></c-gist>";
    }

    public function scrollbar($isCloser = false, $maxHeight = 100, $options = [])
    {
        $options = array_replace([
            'autoHeight'    => true,
            'autoHeightMax' => (int)$maxHeight,
        ], $options);
        return $this->make('scrollbar', $isCloser, $options);
    }

    public function tabs($isCloser = false, $options = [])
    {
        return $this->make('tabs', $isCloser, $options);
    }

    public function tab($isCloser = false, $label = '', $options = [])
    {
        $options[ 'tab' ] = $options[ 'tab' ] ?? $label;
        return $this->make('tab', $isCloser, $options);
    }

    public function row($isCloser = false, $options = [])
    {
        return $this->make('row', $isCloser, $options);
    }

    public function col($isCloser = false, $options = [])
    {
        return $this->make('col', $isCloser, $options);
    }


    protected function props(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    protected function make(string $name, $isCloser = false, $options = [])
    {
        return $isCloser ? "</c-{$name}>" : "<c-{$name} props='{$this->props($options)}'>";
    }
}
