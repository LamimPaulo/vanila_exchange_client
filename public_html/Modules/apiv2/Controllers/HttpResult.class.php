<?php

namespace Modules\apiv2\Controllers;

class HttpResult {
    
    private $sucesso = false;
    private $httpCode = "";
    private $httpMessage = "";
    
    private $body = Array();
    
    public function printResult() {
        $result = Array(
            "sucesso" => $this->sucesso,
            "codigo" => $this->httpCode,
            "mensagem" => $this->httpMessage
        );
        foreach ($this->body as $key=>$dados) {
            $result[$key] = $dados;
        }
        
        header("HTTP/1.1 {$this->httpCode} {$this->httpMessage}");
        header('Content-type: application/json; charset=UTF-8');
        print json_encode($result);
    }
    
    public function setSuccessful($code, $message = "") {
        $this->httpCode = $code;
        if (in_array($code, Array(HTTPResponseCode::$CODE200,HTTPResponseCode::$CODE201,HTTPResponseCode::$CODE202,HTTPResponseCode::$CODE204))) {
            $this->sucesso = true;
        } else {
            if (!in_array($this->httpCode, Array(
                HTTPResponseCode::$CODE400,
                HTTPResponseCode::$CODE401,
                HTTPResponseCode::$CODE403,
                HTTPResponseCode::$CODE404,
                HTTPResponseCode::$CODE405,
                HTTPResponseCode::$CODE408,
                HTTPResponseCode::$CODE413,
                HTTPResponseCode::$CODE415,
                HTTPResponseCode::$CODE422,
                HTTPResponseCode::$CODE429,
                HTTPResponseCode::$CODE500,
            ))) {
                $this->httpCode = HTTPResponseCode::$CODE500;
            }
            
            $this->sucesso = false;
        }
        
        $this->httpMessage = $message;
    }
    
    public function addBody($key, $dados) {
        if ($this->body == null) {
            $this->body = array();
        }
        
        if (isset($this->body[$key])) {
            if (!is_array($this->body[$key])) {
                $info = $this->body[$key];
                $this->body[$key] = Array();
                $this->body[$key][] = $info;
            }
            
            $this->body[$key][] = $dados;
        } else {
            $this->body[$key] = $dados;
        }
        
    }
}