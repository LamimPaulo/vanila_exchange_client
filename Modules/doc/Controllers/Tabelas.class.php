<?php

namespace Modules\doc\Controllers;


class Tabelas {
    
    private  $codigoModulo = "docs";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moedas = $moedaRn->listar("ativo > 0 AND id > 1", "principal DESC, simbolo", null, null);
        $params["moedas"] = $moedas;
        
        \Utils\Layout::view("tabelas", $params);
    }
    
}