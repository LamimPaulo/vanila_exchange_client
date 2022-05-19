<?php

namespace Modules\api\Controllers;

class Buy {
    
    /**
     *
     * @var \Models\Modules\Cadastro\Cliente 
     */
    private $cliente;
    private $body;
    private $headers;
    
    public function index($params) {
        try {
            $method = $_SERVER['REQUEST_METHOD'];

            $this->headers = apache_request_headers();
            $this->body = file_get_contents('php://input');

            if (isset($this->headers["Authorization"])) {

                $authorization = trim(str_replace("Basic", "", $this->headers["Authorization"]));

            } else {

            }
        
        } catch (\Exception $e) {
            $mensagem = \Utils\Excecao::mensagem($e);
            header("HTTP/1.1 {$mensagem}");
        }
        
        
    }
    
}