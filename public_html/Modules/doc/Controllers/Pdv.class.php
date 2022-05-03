<?php

namespace Modules\doc\Controllers;


class Pdv {
    
    private  $codigoModulo = "docs";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        \Utils\Layout::view("pdv", $params);
    }
    
}