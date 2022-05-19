<?php

namespace Modules\apiv2\Controllers;

class Trades {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function index($params) {
        $httpResponse = new HttpResult();
        try {
            
            $simbolo = \Utils\Get::get($params, 0, 0);
            
            if (!is_numeric(strpos($simbolo, ":"))) {
                throw new \Exception("Paridade inválida", 401);
            }
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            try {
                $paridade = $paridadeRn->getBySymbol($simbolo);
            } catch (\Exception $ex) {
                throw new \Exception("Moeda inválida", 401);
            }
            
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $ordens = $ordemExecutadaRn->filtrar($paridade, null, null, "T", "T", 0, 100);
            
            $array = Array();
            foreach ($ordens as $ordem) {
                $array[] = Array(
                    "tipo" => ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "Compra" : "Venda"),
                    "preco" => number_format($ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "volume" => number_format($ordem->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ""),
                    "data" => $ordem->dataExecucao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                );
            }
            
            
            $httpResponse->addBody("trades", $array);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        }
        
        $httpResponse->printResult();
    }
    
}