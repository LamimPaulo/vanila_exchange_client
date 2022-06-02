<?php

namespace Modules\perfil\Controllers;

class Conta {
    
    private  $codigoModulo = "perfil";
    
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        $this->status($params);
    }
    
    public function status($params) {
        try {
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("status_conta", $params);
    }
    
    
    
    
}