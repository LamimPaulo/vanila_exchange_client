<?php

namespace Modules\publica\Controllers;

class Trades {
    private $method = null;
    
    public function __construct() {       
        header('Access-Control-Allow-Origin: *');        
    }
    
    public function index($params) {        
        $httpResponse = new HttpResult();
        try {
            
           $bd = new \Io\BancoDados(BDBOOK);
 
            $simbolo = \Utils\Get::get($params, "market", null);
            $depth = \Utils\Get::get($params, "depth", 20);
            $type = \Utils\Get::get($params, "type", null);
            
            $simbolo = str_replace("_", ":", $simbolo);
            
            if (!is_numeric(strpos($simbolo, ":")) || $simbolo == null) {
                throw new \Exception("Invalid Market.", 401);
            }             
            
            if(empty($depth)){
                $depth = 20;
            }
                       
            if(is_numeric($depth)){
                if($depth > 100){
                    $depth = 100;
                }
            } else {
                throw new \Exception("Invalid Depth Parameter.", 401);
            }            
          
            switch (strtoupper($type)){
                case "BUY":
                    $typeQuery = \Utils\Constantes::ORDEM_COMPRA;
                    break;
                case "SELL":
                    $typeQuery = \Utils\Constantes::ORDEM_VENDA;
                    break;
                case "ALL":
                    $typeQuery = "T";
                    break;
                default:
                    $typeQuery = "T";
                    break;
            }
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn($bd);
            try {
                $paridade = $paridadeRn->getBySymbol($simbolo);
            } catch (\Exception $ex) {
                throw new \Exception("Invalid Market.", 401);
            }
            
            if ($paridade->ativoApi != 1) {
                throw new \Exception("Market is not available.", 401);
            }
            
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn($bd);            
            $ordens = $ordemExecutadaRn->filtrar($paridade, null, null, $typeQuery, "T", 0, $depth);
            
            if(!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0){
               $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
            }
            
            $array = Array();
            foreach ($ordens as $ordem) {
                $array[] = Array(
                    "timestamp" => strtotime($ordem->dataExecucao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                    "market" => str_replace(":", "_", $paridade->symbol),
                    "type" => ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "BUY" : "SELL"),
                    "price" => number_format($ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "volume" => number_format($ordem->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", "")
                );
            }
            
            $httpResponse->addBody("trades", $array);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
}