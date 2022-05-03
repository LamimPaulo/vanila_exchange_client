<?php

namespace Models\Modules\Model;

class Attribute {
    
    /**
     *
     * @var \ReflectionProperty 
     */
    private $property;
    
    
    /**
     *
     * @var Array() 
     */
    private $annotations;
    
    
    public function __construct(\ReflectionProperty  $property) {
        $this->property = $property;
        $this->annotations = CommomRegex::extract($property->getDocComment());
    }
    
    public function isTransiente() {
        
        if(isset($this->annotations[1])) {
            return in_array("Transiente", $this->annotations[1]);
        }
        return false;
    }
    
    
    public function getName() {
        return $this->property->getName();
    }
    
    public function getAnnotations() {
        return $this->annotations;
    }
    
    public function getDatabaseName() {
        $a = lcfirst($this->property->getName());
        
        $field ="";
        for ($i = 0; $i< strlen($a); $i++) {
            $b = substr($a, $i, 1);
            
            if (in_array($b, Array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"))) {
                $b = strtolower("_{$b}");
            }
            
            $field .= $b;
        }
        
        return $field;
    }
    
    
    public function getClass() {
        $package = "";
        $name = "";
        if (isset($this->annotations[1]) && in_array("Package", $this->annotations[1])) {
            $package = trim($this->annotations[2][array_search("Package", $this->annotations[1])]);
            if (!empty($package)) {
                if (substr($package, 0, 1) != "\\") {
                    $package = "\\{$package}";
                }
                
                if (substr($package, sizeof($package) - 2, 1) != "\\") {
                    $package = "{$package}\\";
                }
            }
        }
        
        if (isset($this->annotations[1]) && in_array("var", $this->annotations[1])) {
            $name = trim($this->annotations[2][array_search("var", $this->annotations[1])]);
            if (!empty($name)) {
                if (substr($name, 0, 1) == "\\") {
                    $name = substr($name, 1);
                }
                
                if (substr($name, sizeof($name) - 2, 1) == "\\") {
                    $name = substr($name, 0, sizeof($name) - 2);
                }
            }
        }
        
        return "{$package}{$name}";
    }
}