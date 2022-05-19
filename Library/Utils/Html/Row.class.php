<?php

namespace Utils\Html;

class Row extends AbstractHtmlElement {
    
    protected $fluid = false;
    
    private $content = Array();
    
    public function __construct($classes, $id) {
        parent::__construct($classes, $id);
    }
    
    public function draw() {
        $fluid = ($this->fluid ? "row-fluid" : "");
        $classes = parent::getClasses();
        
        ob_start();
        ?>
        <div class="row <?php echo $fluid ?> <?php echo $classes ?>" <?php echo parent::getId() ?>>
            <?php 
            foreach ($this->content as $e) {
                echo $e->draw();
            }
            ?>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }

    public function add(AbstractHtmlElement $e) {
        $this->content[] = $e;
    }

    public function setClasses($classes) {
        parent::setClasses($classes);
    }

}