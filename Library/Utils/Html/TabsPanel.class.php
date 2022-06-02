<?php

namespace Utils\Html;

class TabsPanel extends AbstractTabsPanel {
    
    const TABS_LEFT = "tabs-left";
    const TABS_RIGHT = "tabs-right";
    
    private $orientacao = "";
    protected $tabs = Array();
    
    public function __construct($orientacao = null, $id = "", $classes = Array()) {
        parent::__construct($classes, $id);
        $this->orientacao = $orientacao;
    }
    
    public function addTab(AbstractTab $tab) {
        $this->tabs[] = $tab;
    }

    public function draw() {
        ob_start();
        ?>
        <div class="tabs-container <?php echo parent::getClasses() ?>" <?php echo parent::getId()?>>
            
            <?php
            if ($this->orientacao == self::TABS_LEFT || $this->orientacao == self::TABS_RIGHT) {
                ?>
                <div class="<?php echo $this->orientacao ?>">
                <?php
            }
            ?>
            
            <ul class="nav nav-tabs">
                <?php 
                foreach ($this->tabs as $tab) {
                    echo $tab->drawHeading();
                }
                ?>
            </ul>
            
            <div class="tab-content">
                <?php 
                foreach ($this->tabs as $tab) {
                    echo $tab->drawContent();
                }
                ?>
            </div>

            <?php
            if ($this->orientacao == self::TABS_LEFT || $this->orientacao == self::TABS_RIGHT) {
                ?>
                </div>
                <?php
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