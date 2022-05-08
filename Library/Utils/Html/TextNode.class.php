<?php

namespace Utils\Html;

class TextNode extends AbstractTextNode {
    
    private $node = "";
    
    public function add($node) {
        $this->node .= "{$node}";
    }

    public function draw() {
        return $this->node;
    }


}