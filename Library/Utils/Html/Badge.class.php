<?php

namespace Utils\Html;

class Badge extends AbstractTextNode {
    
    protected $tipo = "";
    protected $content = "";

    const PRIMARY = "badge-primary";
    const NORMAL = "";
    const SUCCESS = "badge-success";
    const INFO = "badge-info";
    const WARNING = "badge-warning";
    const DANGER = "badge-danger";
    
    public function __construct($tipo = self::NORMAL, $id = "", $classes = Array()) {
        parent::__construct($classes, $id);
        $this->tipo = $tipo;
    }
    
    public function add(AbstractTextNode $e) {
        $this->content .= $e->draw();
    }

    public function draw() {
        $array = Array(
            self::SUCCESS,
            self::INFO,
            self::WARNING,
            self::DANGER,
            self::NORMAL,
            self::PRIMARY
        );
        
        if (!in_array($this->content, $array)) {
            $this->tipo = self::INFO;
        }
        
        ob_start();
        ?>
        <span class="badge <?php echo $this->tipo ?> <?php echo parent::getClasses(); ?>" <?php echo parent::getId(); ?>>
            <?php echo $this->content; ?>
        </span>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function setClasses($classes) {
        parent::setClasses($classes);
    }

}