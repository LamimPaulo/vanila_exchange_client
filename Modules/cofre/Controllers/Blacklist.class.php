<?php

namespace Modules\cofre\Controllers;

class Cofre {
    
    private $codigoModulo = "cofre";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        
        try {
            
            $cliente = \Utils\Geral::getCliente();
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            $saldo = $cofreRn->calcularSaldo($cliente, $moeda);
            
            $params["saldo"] = $saldo;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("blacklist", $params);
    }
        
}