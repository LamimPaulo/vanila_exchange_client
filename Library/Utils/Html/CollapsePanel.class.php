<?php

namespace Utils\Html;

class CollapsePanel extends AbstractHtmlElement {
    
    private $elements = Array();
    
    public function __construct($classes, $id) {
        parent::__construct($classes, $id);
    }
    
    public function add(AbstractHtmlElement $e) {
        $this->elements[] = $e;
    }

    public function draw() {
        $tipo = (empty($tipo) ? Panel::PANEL_DEFAULT : $tipo);
        ob_start();
        ?>
        <div class="panel-group <?php echo parent::getClasses() ?>" <?php echo parent::getId()?>>
            <?php 
            foreach ($this->elements as $e) {
                echo $e->draw();
            }
            ?>
        </div>
        <?php
        $html = ob_get_contents();
        return $html;
    }

    public function setClasses($classes) {
        parent::setClasses($classes);
    }

}