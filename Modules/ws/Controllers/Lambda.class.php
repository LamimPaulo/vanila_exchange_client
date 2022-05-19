<?php

namespace Modules\ws\Controllers;

class Lambda {

    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }

    public function lambdaMain($params) {
        
        $object = "";

        try {
            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Método inválido", 500);
            }
            
            $json = file_get_contents('php://input');
            
            $object = json_decode($json);
                        
            switch ($object->comando) {
                case \Utils\Constantes::QUEUE_COMANDO_USER_CADASTRAR:
                    
                    $this->salvarLog(\Utils\Constantes::QUEUE_COMANDO_USER_CADASTRAR, $json);
                    
                    if($object->parametros->email_valido){
                        \Modules\acesso\Controllers\Cadastro::criarNovoCliente($object);
                    }
                    
                    break;
                    
                case \Utils\Constantes::QUEUE_COMANDO_DADOS_CRIPTOGRAFAR:
                    
                    if(!empty($object->parametros->mensagem)){
                        
                        if(is_array($object->parametros->mensagem)){
                            
                            $mensagem = Array();
                            $dados = $object->parametros->mensagem;
                            
                            foreach($dados as $msg){
                                $mensagem[] = \Utils\Criptografia::encriptyPostId($msg);
                            }
                            
                        } else {
                            $mensagem = \Utils\Criptografia::encriptyPostId($object->parametros->mensagem);
                        }
                        
                        $jsonOut["status"] = "OK";
                        $jsonOut["mensagem"] = $mensagem;
                        
                        print json_encode($jsonOut);
                    }
                    break;
                
                case \Utils\Constantes::QUEUE_COMANDO_DADOS_DESCRIPTOGRAFAR:
                    
                    if(!empty($object->parametros->mensagem)){
                        
                        if(is_array($object->parametros->mensagem)){
                            
                            $mensagem = Array();
                            $dados = $object->parametros->mensagem;
                            
                            foreach($dados as $msg){
                                $mensagem[] = \Utils\Criptografia::decriptyPostId($msg);
                            }
                            
                        } else {
                            $mensagem = \Utils\Criptografia::decriptyPostId($object->parametros->mensagem);
                        }
                        
                        $jsonOut["status"] = "OK";
                        $jsonOut["mensagem"] = $mensagem;
                        
                        print json_encode($jsonOut);
                    }
                    
                    break;

                default:
                    throw new \Exception("Comando inválido", 500);
            }
            
        } catch (\Exception $ex) {
           
            if(is_string($object)){
                $object = json_decode($object);
            }
            
            $mensagem = [
                "codigo" => $ex->getCode(),
                "mensagem" => str_replace("\\", "/", $ex->getMessage()),
                "linha" => str_replace("\\", "/", $ex->getLine()),
                "arquivo" => str_replace("\\", "/", $ex->getFile()),
                "stacktrace" => str_replace("\\", "/", $ex->getTraceAsString()),
                "informacao_adicional" => str_replace("\\", "/", $object->comando)
            ];
            
            if(AMBIENTE == "desenvolvimento"){
                //echo \Utils\Excecao::mensagem($ex);
                \Utils\Notificacao::notificar($mensagem, true, false, null, true);
                
                //echo \Utils\Excecao::mensagem($ex);
            } else {
                \Utils\Notificacao::notificar($mensagem, true, false, null, true);
            }
            
            http_response_code($ex->getCode());
        }
    }
    
    public function salvarLog($carteira, $response){
        $tokenGatewayLogRn = new \Models\Modules\Cadastro\TokenGatewayLogRn();
        $tokenGatewayLog = new \Models\Modules\Cadastro\TokenGatewayLog();
        
        $tokenGatewayLog->endereco = $carteira;
        $tokenGatewayLog->response = $response;
        
        $tokenGatewayLogRn->salvar($tokenGatewayLog);
    }

}
