<?php

namespace Modules\api\Controllers;

class Newsletter { 

    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function novo($params) {
        try {
            $newsletter = new \Models\Modules\Cadastro\NewsLetter();
            $newsletter->id = 0;
            
            $post = file_get_contents("php://input");
            
            if (!sizeof($params["_POST"]) > 0) {
                $j = json_decode($post);
                $newsletter->email = (isset($j->email) ? $j->email : "");
                $newsletter->nome = (isset($j->nome) ? $j->nome : "");
            } else {
                $newsletter->email = \Utils\Post::get($params, "email", NULL);
                $newsletter->nome = \Utils\Post::get($params, "nome", NULL);
            }
            
            if (!\Utils\Validacao::email($newsletter->email)) {
                throw new \Exception("Email inválido");
            }
            
            $newsletterRn = new \Models\Modules\Cadastro\NewsLetterRn();
            $newsletterRn->salvar($newsletter);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function desativar($params) {
        try {
            $newsletter = new \Models\Modules\Cadastro\NewsLetter();
            $newsletter->email = \Utils\Post::get($params, "email", NULL);
            
            $newsletterRn = new \Models\Modules\Cadastro\NewsLetterRn();
            $newsletterRn->desativarEmail($newsletter);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}