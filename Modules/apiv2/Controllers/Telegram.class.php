<?php

namespace Modules\apiv2\Controllers;

class Telegram {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function statusico() {
        $httpResponse = new HttpResult();
        try {
            
            echo 'ok';
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
        echo 'ok1';
    }
    
}