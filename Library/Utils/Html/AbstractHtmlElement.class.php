<?php

namespace Utils\Html;

abstract class AbstractHtmlElement {
    
    protected $classes = "";
    protected $id = "";
    protected $name = "";
    
    protected function __construct($classes, $id) {
        $this->classes = $classes;
        $this->id = $id;
    }


    public abstract function draw();
    public abstract function add(AbstractHtmlElement $e);
    public abstract function setClasses($classes);
    
    public function setId($id){
        $this->id = $id;
    }

    public function getId() {
        return (!empty($this->id) ? "id = '$this->id'" : "");
    }

    protected function getClasses() {
        $classes = "";
        if (is_array($this->classes)) {
            $classes = implode(" ", $this->classes);
        } else {
           $classes = $this->classes; 
        }
        return $classes;
    }
    
    
}