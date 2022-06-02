<?php

namespace Utils\Html;

class Collapsible extends AbstractHtmlElement {
    
    protected $id = "";
    protected $tipo = Panel::PANEL_DEFAULT;
    protected $expanded = false;
    private $elements = Array();
    protected $title = "";
    
    
    public function __construct($title, $tipo = Panel::PANEL_DEFAULT, $classes = Array(), $id = "", $expanded = false) {
        parent::__construct($classes, $id);
        $this->title = $title;
        $this->tipo = $tipo;
        $this->classes = $classes;
        $this->id = $id;
        $this->expanded = $expanded;
    }
    
    public function add(AbstractHtmlElement $e) {
        $this->elements[] = $e;
    }

    public function draw() {
        $tipo = (empty($tipo) ? Panel::PANEL_DEFAULT : $tipo);
        
        $content = "";
        foreach ($this->elements as $e) {
            $content .= $e->draw();
        }
        
        $id = (empty($this->id) ? sha1($content) : $this->id);
        ob_start();
        ?>
        <div class="panel <?php echo $tipo ?> <?php echo parent::getClasses() ?>">
            <div class="panel-heading">
                <h5 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $id ?>" aria-expanded="<?php echo ($this->expanded ? "true" : "false")?>" class="collapsed"><?php echo $this->title ?></a>
                </h5>
            </div>
            <div id="<?php echo $id ?>" class="panel-collapse collapse" aria-expanded="<?php echo ($this->expanded ? "true" : "false")?>">
                <div class="panel-body">
                    <?php echo $content ?>
                </div>
            </div>
        </div>
        <?php
        $html = ob_get_contents();
        return $html;
    }

    public function setClasses($classes) {
        parent::setClasses($classes);
    }

}