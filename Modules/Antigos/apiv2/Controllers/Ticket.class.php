<?php

namespace Modules\apiv2\Controllers;

class Ticket {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    
    public function markets($params) {
        $httpResponse = new HttpResult();
        
        try {
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->listar("ativo = 1", null, null, NULL, FALSE, FALSE);
            
            $lista = Array();
            foreach ($paridades as $paridade) {
                $lista[] = "{$paridade->symbol}";
            }
            
            $httpResponse->addBody("markets", $lista);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
    public function market($params) {
        $httpResponse = new HttpResult();
        
        try {
            $market = \Utils\Get::get($params, 0, NULL);
            
            if (empty($market)) {
                throw new \Exception("Mercado inválido!");
            }
            
            if (!is_numeric(strpos($market, ":"))) {
                $market = "{$market}:BRL";
            }
            
            
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridade = $paridadeRn->getBySymbol($market);

            if ($paridade == null) {
                throw new \Exception("Mercado inválido!");
            }

            $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataInicial->subtrair(0, 0, 1);
            $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));

            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

            $casasDeciamais = ($paridade->idMoedaTrade == 1 ? $configuracao->qtdCasasDecimais : $paridade->moedaTrade->casasDecimais);

            $ticket = Array(
                "currency" => "{$paridade->symbol}",
                "buyPrice" => number_format($paridade->precoCompra, $casasDeciamais, ".", ""),
                "sellPrice" => number_format($paridade->precoVenda, $casasDeciamais, ".", ""),
                "lowPrice" => number_format($paridade->menorPreco, $casasDeciamais, ".", ""),
                "lastPrice" => number_format($paridade->ultimaVenda, $casasDeciamais, ".", ""),
                "highPrice" => number_format($paridade->maiorPreco, $casasDeciamais, ".", ""),
                "volumeCurrency" => number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", ""),
                "volume{$paridade->idMoedaTrade->simbolo}" => number_format(($paridade->volume * $paridade->precoVenda), $casasDeciamais, ".", "")
            );
            
            $httpResponse->addBody("market", $ticket);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
}