<?php
namespace Modules\site\Controllers;
class Site {
    
    public function index($params) {  

        try {

        } catch (\Exception $ex) {

        }
        \Utils\Layout::view("home", $params);
    }

}

