<?php

namespace Modules\api\Controllers;

class Reais {
    
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    
    public function lancamento($params) {
        
        try {
            $token = \Utils\Post::get($params, "token", "");
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $contaCorrenteReais = new \Models\Modules\Cadastro\ContaCorrenteReais();
            $contaCorrenteReais->id = \Utils\Post::get($params, "id", 0);
            $contaCorrenteReais->data = \Utils\Post::get($params, "data", null, "00:00:00");;
            $contaCorrenteReais->descricao = \Utils\Post::get($params, "descricao", null);
            $contaCorrenteReais->idCliente = \Utils\Post::get($params, "idCliente", 0);
            $contaCorrenteReais->tipo = \Utils\Post::get($params, "tipo", NULL);
            $contaCorrenteReais->valor = \Utils\Post::getNumeric($params, "valor", 0);
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
    
}