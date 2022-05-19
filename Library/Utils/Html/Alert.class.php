<?php

namespace Utils\Html;

class Alert extends AbstractHtmlElement {
    
    protected $tipo = "";
    protected $content = Array();
    protected $botaoClose  = false;


    const SUCCESS = "alert-success";
    const INFO = "alert-info";
    const WARNING = "alert-warning";
    const DANGER = "alert-danger";
    
    public function __construct($tipo = self::INFO, $id = "", $botaoClose = false, $classes = Array()) {
        parent::__construct($classes, $id);
        $this->tipo = $tipo;
        $this->botaoClose = $botaoClose;
    }
    
    public function add(AbstractHtmlElement $e) {
        $this->content[] = $e;
    }

    public function draw() {
        $array = Array(
            self::SUCCESS,
            self::INFO,
            self::WARNING,
            self::DANGER
        );
        
        if (!in_array($this->content, $array)) {
            $this->tipo = self::INFO;
        }
        
        ob_start();
        ?>
        <div class="alert <?php echo $this->tipo ?> <?php echo parent::getClasses(); ?>" <?php echo parent::getId(); ?>>
            <?php if ($this->botaoClose) { ?>
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <?php } ?>
                
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

    public function setClasses($classes) {
        parent::setClasses($classes);
    }

}