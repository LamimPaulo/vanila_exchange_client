<?php

namespace Modules\doc\Controllers;


class Contato {
    
    private  $codigoModulo = "docs";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        \Utils\Layout::view("contato", $params);
    }
    
}