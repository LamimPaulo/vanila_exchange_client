<?php

namespace Utils\Html;

class Col extends AbstractHtmlElement {
    
    protected $fluid = false;
    protected $lg = "";
    protected $md = "";
    protected $sm = "";
    protected $xs = "";
    
    protected $lgOffset = "";
    protected $mdOffset = "";
    protected $smOffset = "";
    protected $xsOffset = "";
    
    private $content = Array();
    
    public function __construct($classes, $id) {
        parent::__construct($classes, $id);
    }
    
    public function setSizes($lg, $md, $sm, $xs) {
        $this->lg = $lg;
        $this->md = $md;
        $this->sm = $sm;
        $this->xs = $xs;
    }
    
    public function setOffsets($lgOffset, $mdOffset, $smOffset, $xsOffset) {
        $this->lgOffset = $lgOffset;
        $this->mdOffset = $mdOffset;
        $this->smOffset = $smOffset;
        $this->xsOffset = $xsOffset;
    }
    
    public function draw() {
        $classes = parent::getClasses();
        
        ob_start();
        ?>
        <div class="col <?php echo $this->getSizes() ?> <?php echo $this->getOffsets() ?> <?php echo $classes ?>" <?php echo parent::getId() ?>>
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
    
    protected function getSizes() {
        $lg = (strlen($this->lg) > 0 ? "col-lg-{$this->lg}" : "");
        $md = (strlen($this->md) > 0 ? "col-md-{$this->md}" : "");
        $sm = (strlen($this->sm) > 0 ? "col-sm-{$this->sm}" : "");
        $xs = (strlen($this->xs) > 0 ? "col-xs-{$this->xs}" : "");
        
        if (empty($lg) && empty($md) && empty($sm) && empty($xs)) {
            $xs = "col-xs-12";
        }
        
        return " {$lg} {$md} {$sm} {$xs} ";
    }
    
    protected function getOffsets() {
        $lgOffset = (strlen($this->lgOffset) > 0 ? "col-lg-offset-{$this->lgOffset}" : "");
        $mdOffset = (strlen($this->mdOffset) > 0 ? "col-md-offset-{$this->mdOffset}" : "");
        $smOffset = (strlen($this->smOffset) > 0 ? "col-sm-offset-{$this->smOffset}" : "");
        $xsOffset = (strlen($this->xsOffset) > 0 ? "col-xs-offset-{$this->xsOffset}" : "");
        
        if (empty($lgOffset) && empty($mdOffset) && empty($smOffset) && empty($xsOffset)) {
            return "";
        }
        
        return " {$lgOffset} {$mdOffset} {$smOffset} {$xsOffset} ";
    }
    
    
}