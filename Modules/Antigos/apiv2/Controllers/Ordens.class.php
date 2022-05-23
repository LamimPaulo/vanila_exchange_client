<?php

namespace Modules\apiv2\Controllers;

class Ordens {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
        
        //print_r(apc_cache_info("user"));
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
            
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $compra = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_COMPRA, "N", "N", null, 0, true);
            $venda = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_VENDA, "N", "N", null, 0, true);
            
            $arrayCompra = Array();
            foreach ($compra as $ordemCompra) {
                
                $arrayCompra[] = Array(
                    "preco" => number_format($ordemCompra->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "volume" => number_format($ordemCompra->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "")
                );
            }
            
            $arrayVenda = Array();
            foreach ($venda as $ordemVenda) {
                
                $arrayVenda[] = Array(
                    "preco" => number_format($ordemVenda->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "volume" => number_format($ordemVenda->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "")
                );
            }
            
            $httpResponse->addBody("compra", $arrayCompra);
            $httpResponse->addBody("venda", $arrayVenda);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
    
    
    public function precoMedio($params) {
        $httpResponse = new HttpResult();
        try {
            
            $simbolo = \Utils\Get::get($params, 0, 0);
            $movimento = strtoupper(\Utils\Get::get($params, 1, ""));
            
            if (!is_numeric(strpos($simbolo, ":"))) {
                throw new \Exception("Paridade inválida", 401);
            }
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            try {
                $paridade = $paridadeRn->getBySymbol($simbolo);
            } catch (\Exception $ex) {
                throw new \Exception("Paridade inválida", 401);
            }
            
            $movimentos = Array(
                \Utils\Constantes::ORDEM_COMPRA,
                \Utils\Constantes::ORDEM_VENDA
            );
            
            if (! (in_array($movimento, $movimentos)) ) {
                throw new \Exception("Tipo de movimento inválido. Utilize ".\Utils\Constantes::ORDEM_COMPRA." para book de compra e ".\Utils\Constantes::ORDEM_VENDA." para book de venda.", 401);
            }
            
            $configuracoesRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = $configuracoesRn->get();
            
            $casasDecimais = ($paridade->moedaTrade == 1 ? $configuracao->qtdCasasDecimais : $paridade->moedaTrade->casasDecimais);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $compra = $orderBookRn->getOrders($paridade, $movimento, "N", "N", null, 0, true);
            
            $valorTotalAcumulado = 0;
            $volumeAcumulado = 0;
            
            $book = Array();
            
            $maior = 0;
            $menor = 0;
            
            foreach ($compra as $orderBook) {
                //$orderBook = new \Models\Modules\Cadastro\OrderBook();
                
                $valorTotalAcumulado += number_format(($orderBook->volumeCurrency * $orderBook->valorCotacao), $paridade->moedaBook->casasDecimais, ".", "");
                $volumeAcumulado += number_format($orderBook->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "");
                /*
                $book[] = Array(
                    "preco" => $orderBook->valorCotacao,
                    "volume" => $orderBook->volumeCurrency
                );
                */
                if ($maior < $orderBook->valorCotacao) {
                    $maior = $orderBook->valorCotacao;
                }
                
                if ($menor == 0 || $menor > $orderBook->valorCotacao) {
                    $menor = $orderBook->valorCotacao;
                }
            }
            
            $preco = number_format(($volumeAcumulado > 0 ? ($valorTotalAcumulado / $volumeAcumulado) : 0), $casasDecimais, ".", "");
            
            $httpResponse->addBody("medio", $preco);
            $httpResponse->addBody("menor", number_format($menor, $casasDecimais, ".", ""));
            $httpResponse->addBody("maior", number_format($maior, $casasDecimais, ".", ""));
            $httpResponse->addBody("volume", number_format($volumeAcumulado, $paridade->moedaBook->casasDecimais, ".", ""));
            //$httpResponse->addBody("book", $book);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
}