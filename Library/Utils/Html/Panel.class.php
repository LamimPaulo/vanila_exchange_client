<?php

namespace Utils\Html;

class Panel extends AbstractHtmlElement {
    
    protected $tipo = self::PANEL_DEFAULT;
    private $headingElements = Array();
    private $footerElements = Array();
    
    private $elements = Array();
    
    const PANEL_SUCCESS = "panel-success";
    const PANEL_DANGER = "panel-danger";
    const PANEL_PRIMARY = "panel-primary";
    const PANEL_DEFAULT = "panel-default";
    const PANEL_WARNING = "panel-warning";
    const PANEL_INFO = "panel-info";
    
    public function __construct($tipo, $classes, $id = "") {
        parent::__construct($classes, $id);
        $this->tipo = $tipo;
    }
    
    public function add(AbstractHtmlElement $e) {
        $this->elements[] = $e;
    }
    
    
    public function addHeading(AbstractHtmlElement $e) {
        $this->headingElements[] = $e;
    }
    
    
    public function addFooter(AbstractHtmlElement $e) {
        $this->footerElements[] = $e;
    }

    public function draw() {
        
        $tipo = (empty($tipo) ? self::PANEL_DEFAULT : $tipo);
        ob_start();
        ?>
        <div class="panel <?php echo $tipo?> <?php echo parent::getClasses() ?>"  <?php echo parent::getId()?>>
            
            <?php 
            if (sizeof($this->headingElements) > 0) { 
                foreach ($this->headingElements as $headingElement) {
                    ?>
                    <div class="panel-heading">
                        <?php echo $headingElement->draw(); ?>
                    </div>
                    <?php
                }
            } 
            ?>
            
            <div class="panel-body">
                <?php 
                    if (sizeof($this->elements) > 0) { 
                        foreach ($this->elements as $element) {
                            echo $element->draw();
                        }
                    } 
                ?>
            </div>
            
            <?php 
            if (sizeof($this->footerElements) > 0) { 
                foreach ($this->footerElements as $footerElement) {
                    ?>
                    <div class="panel-footer">
                        <?php echo $footerElement->draw(); ?>
                    </div>
                    <?php
                }
            } 
            ?>
            
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }

    public function setClasses($classes) {
        parent::setClasses($classes);
    }

}