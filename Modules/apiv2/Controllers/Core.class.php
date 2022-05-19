<?php

namespace Modules\apiv2\Controllers;

class Core {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function updatestatus($params) {
        $httpResponse = new HttpResult();
        try {
            
            $usuario = Auth::user();
            
            $j = \Utils\Post::getJson($params, "json", null);
            //exit(print_r($j));
            if (!empty($j)) {
                
                $dados = json_decode($j, false);
                
                if (json_last_error()) {
                    throw new \Exception(json_last_error_msg());
                }
                $statusCoreRn = new \Models\Modules\Cadastro\StatusCoreRn();
                foreach ($dados->cores as $core) {
                    
                    $statusCore = new \Models\Modules\Cadastro\StatusCore();
                    $statusCore->balance = $core->balance;
                    $statusCore->idMoeda = $core->moeda;
                    $statusCore->txcount = $core->txcount;
                    $statusCore->unconfirmedBalance = $core->unconfirmedBalance;
                    $statusCore->walletVersion = $core->walletVersion;
                    
                    $statusCoreRn->salvar($statusCore);
                }
                
            }
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
}