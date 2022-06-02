<?php

namespace Utils\Html;

abstract class AbstractTab extends AbstractHtmlElement{
    
    protected abstract function addHeading($e);
    protected abstract function addContents(AbstractHtmlElement $e);
    protected abstract function drawHeading();
    protected abstract function drawContent();
    
    public function __construct($id) {
        parent::__construct(Array(), $id);
    }
    
    public function add(AbstractHtmlElement $e) {
        
    }

    public function draw() {
        
    }

    public function setClasses($classes) {
        parent::setClasses($classes);
    }
    
    public function getClasses() {
        return parent::getClasses();
    }


}