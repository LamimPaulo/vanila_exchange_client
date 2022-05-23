<?php

namespace Modules\privado\Controllers;

class HttpResult {
    
    private $sucesso = false;
    private $httpCode = "";
    private $httpMessage = "";
    private $httpResult = Array();
    
    private $body = Array();
    
    public function printResult() {
        $result = Array(
            "success" => $this->sucesso,
            "message" => $this->httpMessage, 
            "result" => $this->httpResult
        );
        
        if (!empty($this->body)) {
            /*foreach ($this->body as $dados) {
                $result["result"][0] = $dados;
            }*/
            
            $result["result"] = $this->body;
        } else {
            $result["result"] = null;
        }

        header("HTTP/1.1 {$this->httpCode} {$this->httpMessage}");
        header("cache-control: no-cache");
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
    
    public function addBody($key = null, $dados) {
        if ($this->body == null) {
            $this->body = array();
        }
        
        if(empty($key)){
            $this->body = $dados;
        } else {
            $this->body[$key] = $dados;
        }        
    }
}