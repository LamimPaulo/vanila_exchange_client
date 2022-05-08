<?php

namespace Modules\ws\Controllers;

class Convites {
    
    public function index() {
        error_reporting(E_ALL);
        try {
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar(null, "id");
            
            foreach ($clientes as $c) {
                //\Lahar\Cadastro::novo($c);
                \Lahar\Cadastro::estagioLead($c, 4);
                
                if ($c->fotoClienteVerificada > 0 && $c->fotoDocumentoVerificada > 0 && $c->fotoResidenciaVerificada > 0) {
                    \Lahar\Cadastro::estagioLead($c, 2);
                }
            }
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
    }
    
    
    
}