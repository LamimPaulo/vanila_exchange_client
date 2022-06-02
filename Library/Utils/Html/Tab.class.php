<?php

namespace Utils\Html;

class Tab extends AbstractTab {
    private $id = "";
    private $headingElements = Array();
    private $contentElements = Array();
    
    private $active = false;
    private $expanded = false;
    
    public function __construct($id = "", $active = false, $expanded = false) {
        parent::__construct($id);
        $this->active = $active;
        $this->expanded = $expanded;
        $this->id = $id;
    }
    
    protected function addContents(AbstractHtmlElement $e) {
        $this->contentElements[] = $e;
    }

    protected function addHeading($e) {
        $this->headingElements[] = $e;
    }
    
    private function getHeading() {
        $heading = "";
        foreach ($this->headingElements as $h) {
            if ($h instanceof AbstractHtmlElement) {
                $heading .= " {$h->draw()} ";
            } else {
                $heading .= " {$h} ";
            }
        }
    }
    
    public function setClasses($classes) {
        parent::setClasses($classes);
    }

    protected function drawContent() {
        $heading = $this->getHeading();
        $id = (empty($this->id) ? sha1($heading) : $this->id);
        ob_start();
        ?>
        <div id="<?php echo $id ?>" class="tab-pane <?php echo ($this->active ? "class='active'" : "") ?>">
            <div class="panel-body">
                <?php 
                    foreach ($this->contentElements as $e) {
                        echo $e->draw();
                    }
                ?>
            </div>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    protected function drawHeading() {
        $heading = $this->getHeading();
        $id = (empty($this->id) ? sha1($heading) : $this->id);
        ob_start();
        ?>
        <li <?php echo ($this->active ? "class='active'" : "") ?>><a data-toggle="tab" href="#<?php echo $id ?>" aria-expanded="true"><?php echo $heading ?></a></li>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

}