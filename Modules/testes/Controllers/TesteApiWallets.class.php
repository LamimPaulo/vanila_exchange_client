<?php

namespace Modules\testes\Controllers;

class TesteApiWallets {
    
    public function prodCallback() {
        try {
            
            $ticker = \Exchanges\MercadoBitcoin::ticker();
            exit(print_r($ticker));
            
            /*
            $usuarioVagner = new \Models\Modules\Cadastro\Usuario(Array("id" => 1482811575));

            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuarioRn->conexao->carregar($usuarioVagner);

            $msg = "Chamada Callback Produção Api Carteiras Remotas Efetuada com sucesso!.";

            $cel3 = str_replace(Array(" ", "-", "(", ")"), "", $usuarioVagner->celular);


            $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
            $api->EnviaSMS("55{$cel3}", $msg);
            
            
            echo "OK";*/
        } catch (\Exception $ex) {
            print_r($ex);
        }
    }
    public function homologCallback() {
        try {
            $usuarioVagner = new \Models\Modules\Cadastro\Usuario(Array("id" => 1482811575));

            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuarioRn->conexao->carregar($usuarioVagner);

            $msg = "Chamada Callback Homolog Api Carteiras Remotas Efetuada com sucesso!.";

            $cel3 = str_replace(Array(" ", "-", "(", ")"), "", $usuarioVagner->celular);


            $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
            $api->EnviaSMS("55{$cel3}", $msg);
            
            
            echo "OK";
        } catch (\Exception $ex) {
            print_r($ex);
        }
    }
    
}