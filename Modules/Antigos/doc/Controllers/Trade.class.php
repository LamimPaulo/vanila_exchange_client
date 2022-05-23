<?php

namespace Modules\doc\Controllers;


class Trade {
    
    private  $codigoModulo = "docs";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        \Utils\Layout::view("trade", $params);
    }
    
}