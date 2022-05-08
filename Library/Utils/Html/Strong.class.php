<?php

namespace Utils\Html;

class Strong extends AbstractHtmlElement {
    
    private $text = "";
    
    protected function __construct($text, $id = "", $classes = Array()) {
        parent::__construct($classes, $id);
        $this->text = $text;
    }
    
    public function add(AbstractHtmlElement $e) {
        $this->text .= " {$e->draw()} ";
    }

    public function draw() {
        ob_start();
        ?>
        <strong class="<?php echo parent::getClasses()?>" <?php echo parent::getId()?>>
            <?php echo $this->text ?>
        </strong>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function setClasses($classes) {
        parent::setClasses($classes);
    }

}