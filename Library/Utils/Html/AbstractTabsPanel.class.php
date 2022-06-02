<?php

namespace Utils\Html;

abstract class AbstractTabsPanel extends AbstractHtmlElement{
    
    protected abstract function addTab(AbstractTab $tab);
    protected abstract function draw();
    
    
    
    public function add(AbstractHtmlElement $e) {
        
    }


    public function setClasses($classes) {
        parent::setClasses($classes);
    }
    
    public function getClasses() {
        return parent::getClasses();
    }

}