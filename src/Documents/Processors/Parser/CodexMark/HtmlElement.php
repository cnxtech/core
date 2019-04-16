<?php

namespace Codex\Documents\Processors\Parser\CodexMark;

class HtmlElement extends \League\CommonMark\HtmlElement
{
    public function __toString()
    {
        $result = '<' . $this->tagName;

        foreach ($this->attributes as $key => $value) {
            if($key === 'props'){
                if(!is_string($value)){
                    $value = json_encode($value, JSON_UNESCAPED_SLASHES);
                }
                $result .= ' ' . $key . "='" . $value . "'";
                continue;
            }
            $result .= ' ' . $key . '="' . $value . '"';
        }

        if ($this->contents !== '') {
            $result .= '>' . $this->getContents() . '</' . $this->tagName . '>';
        } elseif ($this->selfClosing) {
            $result .= ' />';
        } else {
            $result .= '></' . $this->tagName . '>';
        }

        return $result;
    }
}
