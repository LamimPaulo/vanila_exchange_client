<?php

namespace Utils\Html;

abstract class AbstractTextNode extends AbstractHtmlElement{
    
    public abstract function add($node);
    public abstract function draw();
    
    public function setClasses($classes) {
        ;
    }
    
}