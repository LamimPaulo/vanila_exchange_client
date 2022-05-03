<?php

namespace Modules\doc\Controllers;


class Newsletter {
    
    private  $codigoModulo = "docs";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        \Utils\Layout::view("newsletter", $params);
    }
    
}