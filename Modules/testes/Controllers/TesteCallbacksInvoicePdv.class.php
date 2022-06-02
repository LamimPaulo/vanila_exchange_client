<?php

namespace Modules\testes\Controllers;

class TesteCallbacksInvoicePdv {
    
    private function autenticar($params) {
        ob_start();
        $auth = new \Modules\api\Controllers\Auth();
        $params["_POST"] = Array(
            "email" => "renato.oliva@outlook.com",
            "senha" => "123"
        );
        $auth->index($params);
        $content = ob_get_contents();
        ob_end_clean();
        
        $json = json_decode($content);
        
        return $json->token;
    }
    
    public function teste($params) {
        //http://localhost/simulacaoCallback/invoice
        
        try {
       
            $nome = "Renato";
            $email = "renatoju3@hotmail.com";
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($email);
            
            //\Lahar\Cadastro::novo($cliente);
            \Lahar\Cadastro::estagioLead($cliente, 3);
        
            /*
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar(null, "id");
            
            foreach ($clientes as $c) {
                \Lahar\Cadastro::novo($c);
                \Lahar\Cadastro::estagioLead($c, 2);
                
                if ($c->fotoClienteVerificada > 0 && $c->fotoDocumentoVerificada > 0 && $c->fotoResidenciaVerificada > 0) {
                    \Lahar\Cadastro::estagioLead($c, 3);
                }
            }
            */
        } catch (\Exception $ex) {
            exit(print_r($ex));
        }
    }
    
    public function callbackProducao() {
        exit("Chamou o callback producao");
    }
    
    public function callbackHomologacao() {
        exit("Chamou o callback homologacao");
    }
    
    public function callbackInvoice() {
        exit("Chamou o callback invoice");
    }
    
    
}




