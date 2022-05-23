<?php

namespace Modules\publica\Controllers;
use Predis\Client;
class MarketHistory {
    private $method = null;

    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }

    public function history($params) {

        $httpResponse = new HttpResult();
        $storage_history = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));

        try {

            $bd = new \Io\BancoDados(BD);

            $market = strtoupper(\Utils\Get::get($params, "market", NULL));
            $mkt = $market;


            if (empty($market)) {
                throw new \Exception("Invalid Market.");
            }

            if(empty($count)){
                $count = 100;
            }

            if(is_numeric($count)){
                if($count > 100){
                    $count = 100;
                }
            } else {
                throw new \Exception("Invalid Count Parameter.", 401);
            }

            if (!is_numeric(strpos($market, "_"))) {
                $market = "{$market}_BRL";
            }

            if($storage_history->exists('HISTORY_' .  $mkt)){
                $history[] = $storage_history->hgetall('HISTORY_' .  $mkt);
                $dados = json_decode($history[0][0]);
                $httpResponse->addBody(null, array_splice($dados,0,$count,$dados));
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            }else{

                $market = str_replace("_", ":", $market);
                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn($bd);

                $paridade = $paridadeRn->getBySymbol($market);
                if ($paridade == null) {
                    throw new \Exception("Invalid Market.", 401);
                }

                if ($paridade->ativo != 1) {
                    throw new \Exception("Market is not available.", 401);
                }

                if(!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0){
                    $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
                }

                $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn($bd);
                $ordens = $ordemExecutadaRn->filtrar($paridade, null, null, "T", "T", 0, 1000);

                $ordensExecutadas = Array();
                foreach ($ordens as $ordem) {

                    $array = Array(
                        "tradeID"=> $ordem->id,
                        "timestamp"=> strtotime($ordem->dataExecucao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                        "orderType"=> ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "BUY" : "SELL"),
                        "price"=> number_format($ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "quantity"=> number_format($ordem->volumeExecutado, $paridade->moedaBook->casasDecimais, ",", "."),
                        "baseVolume"=> number_format(($ordem->volumeExecutado * $ordem->valorCotacao), $paridade->moedaBook->casasDecimais, ".", ""),
                        "total"=> number_format(($ordem->volumeExecutado * $ordem->valorCotacao), $paridade->moedaBook->casasDecimais, ".", "")
                    );

                    $ordensExecutadas[] = $array;
                }

                $storage_history->hmset('HISTORY_' .  $mkt, array(json_encode($ordensExecutadas)));
                $storage_history->expire('HISTORY_' .  $mkt,60);

                if($storage_history->exists('HISTORY_' .  $mkt)){
                    $history[] = $storage_history->hgetall('HISTORY_' .  $mkt);
                    $dados = json_decode($history[0][0]);
                    $httpResponse->addBody(null, array_splice($dados,0,$count,$dados));
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                }else {
                    throw new \Exception("Market is not available.", 400);
                }
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE404, \Utils\Excecao::mensagem($ex));
        }

        $httpResponse->printResult();
    }

}