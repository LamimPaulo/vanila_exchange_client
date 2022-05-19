<?php

namespace Modules\api\Controllers;

class Signup {

    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("recuperar", IDIOMA);
        header('Access-Control-Allow-Origin: *');
    }
    
//    public function index($params) {
//        try {
//            
//            $nome = \Utils\Post::get($params, "nome", null);
//            $email = \Utils\Post::get($params, "email", null);
//            $referencia = \Utils\Post::get($params, "referencia", NULL);
//            
//            $params["_POST"] = Array(
//                "nome" => $nome,
//                "email" => $email,
//                "referencia" => $referencia
//            );
//            
//            $controllerCadastro = new \Modules\acesso\Controllers\Cartoes($params);
//            $controllerCadastro->cadastro($params);
//            
//            
//            $json["sucesso"] = true;
//            $json["mensagem"] = $this->idioma->getText("cadastroSucesso") ." "."{$email}." ;
//        } catch (\Exception $ex) {
//            $json["sucesso"] = false;
//            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
//        }
//        print json_encode($json);
//    }
    
}