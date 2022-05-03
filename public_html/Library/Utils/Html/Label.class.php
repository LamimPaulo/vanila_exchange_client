<?php

namespace Utils\Html;

class Label extends AbstractTextNode {
    
    protected $tipo = "";
    protected $content = "";

    const SIMPLE_LABEL = "simple-label";
    const PRIMARY = "label-primary";
    const SUCCESS = "label-success";
    const INFO = "label-info";
    const WARNING = "label-warning";
    const DANGER = "label-danger";
    const NORMAL = "";
    
    public function __construct($tipo = self::SIMPLE_LABEL, $id = "", $classes = Array()) {
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
            self::SIMPLE_LABEL,
            self::NORMAL,
            self::PRIMARY
        );
        
        if (!in_array($this->content, $array)) {
            $this->tipo = self::SIMPLE_LABEL;
        }
        
        $classe = "";
        if ($this->tipo != self::SIMPLE_LABEL) {
            $classe = "label {$this->tipo}";
        }
        
        ob_start();
        ?>
        <span class="<?php echo $classe ?> <?php echo parent::getClasses(); ?>" <?php echo parent::getId(); ?>>
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