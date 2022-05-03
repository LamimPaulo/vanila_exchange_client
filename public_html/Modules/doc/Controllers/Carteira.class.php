<?php

namespace Modules\doc\Controllers;


class Carteira {
    
    private  $codigoModulo = "docs";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        \Utils\Layout::view("carteira", $params);
    }
    
}