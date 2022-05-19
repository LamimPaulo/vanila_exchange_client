<?php

namespace Modules\api\Controllers;

class Core {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function updatestatus($params) {
        
        try {
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            
            
            $j = \Utils\Post::getJson($params, "json", null);
          
            
            if (!empty($j)) {
                
                $dados = json_decode($j, false);
                
                if (json_last_error()) {
                    throw new \Exception(json_last_error_msg());
                }
                $statusCoreRn = new \Models\Modules\Cadastro\StatusCoreRn();
                foreach ($dados->cores as $core) {
                    $moeda = \Models\Modules\Cadastro\MoedaRn::get($core->moeda);
                    $statusCore = new \Models\Modules\Cadastro\StatusCore();
                    $statusCore->balance = number_format($core->balance, $moeda->casasDecimais, ".", "");
                    $statusCore->idMoeda = $core->moeda;
                    $statusCore->txcount = ($core->txcount > 0 ? $core->txcount : 0);
                    $statusCore->unconfirmedBalance = number_format($core->unconfirmedBalance, $moeda->casasDecimais, ".", "");
                    $statusCore->walletVersion = ($core->walletVersion != null ? $core->walletVersion : "");
                    $statusCore->dataUltimaAtualizacaoCore = (isset($core->dataAtualizacao) && strlen($core->dataAtualizacao) == 19 ? new \Utils\Data($core->dataAtualizacao) : null);
                    
                    $statusCoreRn->salvar($statusCore);
                }
                
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["erro"] = $ex->getCode();
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
}
?>