<?php

namespace Modules\api\Controllers;

class Contato { 

    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function send($params) {
        try {
            $contato = new \Models\Modules\Cadastro\ContatoSite();
            $contato->id = 0;
            $contato->departamento = \Utils\Post::get($params, "departamento", NULL);
            $contato->email = \Utils\Post::get($params, "email", NULL);
            $contato->mensagem = \Utils\Post::get($params, "mensagem", NULL);
            $contato->nome = \Utils\Post::get($params, "nome", NULL);
            $contato->telefone = \Utils\Post::get($params, "telefone", NULL);
            
            $contatoRn = new \Models\Modules\Cadastro\ContatoSiteRn();
            $contatoRn->salvar($contato);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["sucesso"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    
}