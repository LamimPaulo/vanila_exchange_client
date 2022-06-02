<?php
namespace Modules\apiv2\Controllers;

class Auth {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
   
    public function index() {
        $httpResponse = new HttpResult();
        try {
            
            $method = $_SERVER['REQUEST_METHOD'];
            if (strtoupper($method) != "POST") {
                throw new \Exception("Método não permitido", 405);
            }
            
            $this->headers = apache_request_headers();
            
            if (isset($this->headers["Authorization"])) {

                $authorization = trim(str_replace("Basic", "", $this->headers["Authorization"]));
                
                $cliente = $this->logar($authorization);
                
                if ($cliente->status == 0) {
                    throw new \Exception("Cadastro em análise", 401);
                }
                
                if ($cliente->status == 2) {
                    throw new \Exception("Não Autorizado. Cadastro bloqueado", 401);
                }
                
                $c = Array(
                    "codigo" => \Utils\Criptografia::encriptyPostId($cliente->id),
                    "nome" => $cliente->nome,
                    "email" => $cliente->email,
                    "celular" => $cliente->celular,
                    "clienteDesde" => $cliente->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
                    "foto" => URLBASE_CLIENT . UPLOADS . $cliente->foto,
                    "sexo" => $cliente->sexo
                );
                
                $httpResponse->addBody("cliente", $c);
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Não Autorizado", 401);
            }
            
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
    public static function authenticate() {
        $this->headers = apache_request_headers();
            
        if (isset($this->headers["Authorization"])) {
            $authorization = trim(str_replace("Basic", "", $this->headers["Authorization"]));
            $cliente = $this->logar($authorization);
            
            if ($cliente->status == 0) {
                    throw new \Exception("Cadastro em análise", 401);
            }

            if ($cliente->status == 2) {
                throw new \Exception("Não Autorizado. Cadastro bloqueado", 401);
            }
            
            return $cliente;
        } else {
            throw new \Exception("Não Autorizado", 401);
        }
    }
    
    private static function logar($authorization) {
        if (empty($authorization)) {
            throw new \Exception("Solicitação Inválida", 400);
        }
        
        $auth = base64_decode($authorization);
        $dados = explode(":", $auth);
        
        if (sizeof($dados) != 2 || empty($dados[0]) || empty($dados[1])) {
            throw new \Exception("Solicitação Inválida", 400);
        }
        
        try {
            \Utils\SQLInjection::clean($dados[0]);
            \Utils\SQLInjection::clean($dados[1], false);
        } catch(\Exception $ex) {
            throw new \Exception("Solicitação Inválida", 400);
        }
        
        $dados[0] = $dados[0];
        $dados[1] = sha1($dados[1].\Utils\Constantes::SEED_SENHA);
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->select(Array(
            "email" => $dados[0],
            "senha" => $dados[1]
        ));
        
        if (sizeof($result) <= 0) {
            throw new \Exception("Não Autorizado", 401);
        }
        
        $cliente = $result->current();
        return $cliente;
    }
    
    public static function auth($headers) {
        $authorization = "";
        if (isset($headers["Authorization"]) || isset($headers["authorization"])) {
            $authorization = trim(str_replace("Basic", "", (isset($headers["Authorization"]) ? $headers["Authorization"] : $headers["authorization"])));
        }
        
        return Auth::logar($authorization);
    }
    
    /**
     * 
     * @return \Models\Modules\Cadastro\Usuario
     * @throws \Exception
     */
    public static function user() {
        
        $this->headers = apache_request_headers();
            
        if (isset($this->headers["Authorization"])) {
            $authorization = trim(str_replace("Basic", "", $this->headers["Authorization"]));
            
            if (empty($authorization)) {
                throw new \Exception("Solicitação Inválida", 400);
            }

            $auth = base64_decode($authorization);
            $dados = explode(":", $auth);

            if (sizeof($dados) != 2 || empty($dados[0]) || empty($dados[1])) {
                throw new \Exception("Solicitação Inválida", 400);
            }

            try {
                \Utils\SQLInjection::clean($dados[0]);
                \Utils\SQLInjection::clean($dados[1]);
            } catch(\Exception $ex) {
                throw new \Exception("Solicitação Inválida", 400);
            }

            $dados[0] = $dados[0];
            $dados[1] = sha1($dados[1].\Utils\Constantes::SEED_SENHA);

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $result = $clienteRn->conexao->select(Array(
                "email" => $dados[0],
                "senha" => $dados[1]
            ));

            if (sizeof($result) <= 0) {
                throw new \Exception("Não Autorizado", 401);
            }

            $usuario = $result->current();
            
            if ($usuario->status == 0) {
                    throw new \Exception("Cadastro em análise", 401);
            }

            if ($usuario->status == 2) {
                throw new \Exception("Não Autorizado. Cadastro bloqueado", 401);
            }
            
            return $usuario;
        } else {
            throw new \Exception("Não Autorizado", 401);
        }
        
    }
    
}