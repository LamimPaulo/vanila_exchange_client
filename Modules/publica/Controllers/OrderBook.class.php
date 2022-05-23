<?php

namespace Modules\publica\Controllers;
use Predis\Client;

class OrderBook {
    private $method = null;
    
    public function __construct() {        
        header('Access-Control-Allow-Origin: *');
    }
    
    public function getOrderbook($params) {
        
        $httpResponse = new HttpResult();
        $storage_orderBook = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));

        try {
            
            $market = strtoupper(\Utils\Get::get($params, "market", NULL));
            
            if (empty($market)) {
                throw new \Exception("Invalid Market.", 401);
            }
            $coin = $market;
            $market = str_replace("_", ":", $market);
            
            $type = strtoupper(\Utils\Get::get($params, "type", NULL));
            
            if ($type == null) {
                throw new \Exception("Invalid type.");
            }
            
            $limit = \Utils\Get::get($params, "depth", 20);
            
            if(empty($limit)){
                $limit = 20;
            }
                       
            if(is_numeric($limit)){
                if($limit > 1000){
                    $limit = 1000;
                }
            } else {
                throw new \Exception("Invalid Depth Parameter.", 401);
            }

            if($storage_orderBook->exists('ORDERBOOK_' .  $coin . '_' . $type)){
                $orderbook[] = $storage_orderBook->hgetall('ORDERBOOK_' .  $coin . '_' . $type);
                $dados = json_decode($orderbook[0][0]);
                $httpResponse->addBody(null, $dados);
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            }else {

                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridade = $paridadeRn->getBySymbol($market);

                if ($paridade == null) {
                    throw new \Exception("Invalid Market.");
                }


                $compraAtivo = false;
                $vendaAtivo = false;

                switch (strtoupper($type)){
                    case "BUY":
                        $compraAtivo = true;
                        break;
                    case "SELL":
                        $vendaAtivo = true;
                        break;
                    case "ALL":
                        $compraAtivo = true;
                        $vendaAtivo = true;
                        break;
                    default:
                        throw new \Exception("Invalid type.");
                        break;
                }

                if(!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0){
                    $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
                }

                $bdBook = new \Io\BancoDados(BD);
                $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn($bdBook);
                $arrayOrderBook = [];

                if ($compraAtivo) {
                    $compra = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_COMPRA, "N", "N", 1000, 0, false);
                    $arrayCompra = Array();
                    foreach ($compra as $ordemCompra) {
                        if (number_format($ordemCompra->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "") > 0.0) {
                            $arrayCompra[] = array(
                                "timestamp" => strtotime(date("Y-m-d H:i:s")),
                                "price" => number_format($ordemCompra->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                                "quantity" => number_format($ordemCompra->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "")
                            );
                        }
                    }
                    $arrayOrderBook['buy'] = array_splice($arrayCompra,0,$limit,$arrayCompra);

                }

                if ($vendaAtivo) {
                    $venda = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_VENDA, "N", "N", 1000, 0, false);
                    $arrayVenda = Array();
                    foreach ($venda as $ordemVenda) {
                        if (number_format($ordemVenda->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "") > 0.0) {
                            $arrayVenda[] = Array(
                                "timestamp" => strtotime(date("Y-m-d H:i:s")),
                                "price" => number_format($ordemVenda->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                                "quantity" => number_format($ordemVenda->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "")
                            );
                        }


                    }
                    $arrayOrderBook['sell'] = array_splice($arrayVenda,0,$limit,$arrayVenda);
                    }

                $storage_orderBook->hmset('ORDERBOOK_' .  $coin . '_' . $type, array(json_encode($arrayOrderBook)));
                $storage_orderBook->expire('ORDERBOOK_' .  $coin . '_' . $type,60);

                if($storage_orderBook->exists('ORDERBOOK_' .  $coin . '_' . $type)){
                    $history[] = $storage_orderBook->hgetall('ORDERBOOK_' .  $coin . '_' . $type);
                    $dados = json_decode($history[0][0]);
                    $httpResponse->addBody(null, $dados);
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                }else {
                    throw new \Exception("Market is not available.", 400);
                }
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
}