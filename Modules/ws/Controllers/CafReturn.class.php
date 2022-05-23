<?php

namespace Modules\ws\Controllers;

class CafReturn {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function inbound() {
        
        $httpResponse = new \Modules\apiv2\Controllers\HttpResult();
        
        try {
            $json = json_decode( file_get_contents('php://input'), true);

            $rabbit =  new \RabbitMq\Client();
            $rabbit->sendQueue('user_kyc_response', $json);

            $httpResponse->setSuccessful(\Modules\apiv2\Controllers\HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
         $httpResponse->printResult();
    }

    
}
