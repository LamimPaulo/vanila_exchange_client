<?php

namespace Modules\publica\Controllers;
use Predis\Client;

class Ticker {
    private $method = null;
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }

    public function markets() {
        $httpResponse = new HttpResult();
        $storage_markets = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));

        if($storage_markets->exists('MARKETS')){
            $market[] = $storage_markets->hgetall('MARKETS');
            $httpResponse->addBody(null, json_decode($market[0][0]));
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        }else{
            try {
                $data = new \Utils\Data(date("Y-m-d H:i:s"));
                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridades = $paridadeRn->listar("ativo = 1 && ativo_api = 1 ", "symbol ASC", null, NULL, true, true);

                foreach ($paridades as $paridade) {

                    if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
                        $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
                    }

                    $market = Array(
                        "timestamp" => strtotime($data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                        "marketName"=> str_replace(":", "_", $paridade->symbol),
                        "marketAsset"=> $paridade->moedaBook->simbolo,
                        "baseAsset"=> $paridade->moedaTrade->simbolo,
                        "marketAssetLong"=> $paridade->moedaBook->nome,
                        "baseAssetLong"=> $paridade->moedaTrade->nome,
                        "isActive"=> $paridade->statusMercado == 1 ? true : false,
                        "minTradeSize"=> number_format($paridade->precoMinimo, $paridade->moedaBook->casasDecimais, ".", ""),
                        "ask" => number_format($paridade->precoVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "bid" => number_format($paridade->precoCompra, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "last" => number_format($paridade->ultimaVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "spread" => number_format((100 * (($paridade->precoVenda - $paridade->precoCompra) / $paridade->precoCompra)), 4, ".", ""),
                        "low24h" => number_format($paridade->menorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "high24h" => number_format($paridade->maiorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "vol24h" => number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", ""),
                        "quoteVolume" => number_format($paridade->quoteVolume, $paridade->moedaBook->casasDecimais, ".", ""),
                        "isFrozen" => $paridade->statusMercado == 1 ? 0 : 1  ,
                        "marketImageData" =>  getenv("EnvUrlProd")  . "/resources/images/currencies/" . $paridade->moedaBook->icone,
                        "baseImageData" => getenv("EnvUrlProd") . "/resources/images/currencies/" . $paridade->moedaTrade->icone,
                        "infoMessage"=> "This market can be removed due to the low volume."
                    );

                    $lista []= $market;
                    $market = null;
                }

                $storage_markets->hmset('MARKETS', array(json_encode($lista)));
                $storage_markets->expire('MARKETS',60);


                if($storage_markets->exists('MARKETS')){
                    $markets[] = $storage_markets->hgetall('MARKETS');
                    $httpResponse->addBody(null, json_decode($markets[0][0]));
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                }else {
                    throw new \Exception("Markets is not available.", 400);
                }
            } catch (\Exception $ex) {
                $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
            }
        }
        $httpResponse->printResult();
    }

    public function ticker($params) {

        $httpResponse = new HttpResult();
        $storage_ticker = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));
        
        try {
            $market = strtoupper(\Utils\Get::get($params, "market", NULL));
            
            if (empty($market)) {
                throw new \Exception("Invalid Market.");
            }
            $mercado = $market;
            $market = str_replace("_", ":", $market);

            if($storage_ticker->exists('TICKER_' .  $mercado)){

                $ticker[] = $storage_ticker->hgetall('TICKER_' .  $mercado);
                $httpResponse->addBody(null, $ticker);
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);

            }else{

                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridade = $paridadeRn->getBySymbol($market);
                if ($paridade == null) {
                    throw new \Exception("Invalid Market.");
                }

                if ($paridade->ativo != 1) {
                    throw new \Exception("Market is not available.", 400);
                }

                $data = new \Utils\Data(date("Y-m-d H:i:s"));
                if(!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0){
                    $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
                }

                $storage_ticker->hmset('TICKER_' .  $mercado, array(
                        "timestamp" => strtotime($data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                        "market" => str_replace(":", "_", $paridade->symbol),
                        "ask" => number_format($paridade->precoVenda, $paridade->casasDecimaisMoedaTrade, ".", ""),
                        "bid" => number_format($paridade->precoCompra, $paridade->casasDecimaisMoedaTrade, ".", ""),
                        "last" => number_format($paridade->ultimaVenda, $paridade->casasDecimaisMoedaTrade, ".", ""),
                        "spread" => number_format((100 * (($paridade->precoVenda - $paridade->precoCompra) / $paridade->precoCompra)), 4, ".", ""),
                        "low24h" => number_format($paridade->menorPreco, $paridade->casasDecimaisMoedaTrade, ".", ""),
                        "high24h" => number_format($paridade->maiorPreco, $paridade->casasDecimaisMoedaTrade, ".", ""),
                        "vol24h" => number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", ""),
                        "quoteVolume" => number_format($paridade->quoteVolume, $paridade->moedaBook->casasDecimais, ".", ""),
                        "marketImageData" => URL_IMAGE . $paridade->moedaBook->icone,
                        "baseImageData" => URL_IMAGE . $paridade->moedaTrade->icone,
                        "isFrozen" => $paridade->statusMercado == 1 ? 0 : 1)
                );

                $storage_ticker->expire('TICKER_' .  $mercado,2);

                if($storage_ticker->exists('TICKER_' .  $mercado)){
                    $ticker[] = $storage_ticker->hgetall('TICKER_' . $mercado);
                    $httpResponse->addBody(null, $ticker);
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                }else {
                    throw new \Exception("Market is not available.", 400);
                }

            };

        } catch (\Exception $ex) {
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE404, \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();

    }

    public function marketSummary($params) {

        $httpResponse = new HttpResult();
        $storage_marketSummary = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));

        try {
            $market = strtoupper(\Utils\Get::get($params, "market", NULL));

            if (empty($market)) {
                throw new \Exception("Invalid Market.");
            }

            $market = str_replace("_", ":", $market);

            if($storage_marketSummary->exists('MARKETSUMMARY_' .  $market)){

                $marketSummary[] = $storage_marketSummary->hgetall('MARKETSUMMARY_' .  $market);
                $httpResponse->addBody(null, $marketSummary);
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);

            }else{

                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridade = $paridadeRn->getBySymbol($market);

                if ($paridade == null) {
                    throw new \Exception("Invalid Market.");
                }

                if ($paridade->ativoApi != 1) {
                    throw new \Exception("Market is not available.", 400);
                }

                if(!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0){
                    $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
                }

                $storage_marketSummary->hmset('MARKETSUMMARY_' .  $market, array(
                    "timestamp" => strtotime(date("Y-m-d H:i:s")),
                    "marketName" => str_replace(":", "_", $paridade->symbol),
                    "marketAsset" => $paridade->moedaBook->simbolo,
                    "baseAsset" => $paridade->moedaTrade->simbolo,
                    "marketAssetName" => $paridade->moedaBook->nome,
                    "baseAssetName" => $paridade->moedaTrade->nome,
                    "high" => number_format($paridade->maiorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "low" => number_format($paridade->menorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "last" => number_format($paridade->ultimaVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "volume" => number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", ""),
                    "baseVolume" => number_format($paridade->moedaTrade->volume, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "bid" => number_format($paridade->precoCompra, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "ask" => number_format($paridade->precoVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "isActive" => $paridade->statusMercado == 1 ? true : false,
                    "marketImageData" =>  getenv("EnvUrlProd")  . "/resources/images/currencies/" . $paridade->moedaBook->icone,
                    "baseImageData" => getenv("EnvUrlProd") . "/resources/images/currencies/" . $paridade->moedaTrade->icone,
                    "infoMessage" => "This market can be removed due to the low volume."
                ));


                $storage_marketSummary->expire('MARKETSUMMARY_' .  $market,60);

                if($storage_marketSummary->exists('MARKETSUMMARY_' .  $market)){

                    $marketSummary[] = $storage_marketSummary->hgetall('MARKETSUMMARY_' .  $market);
                    $httpResponse->addBody(null, $marketSummary);
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

    public function marketSummaries($params) {
        $httpResponse = new HttpResult();
        $storage_marketSummaries = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));

        try {

            $basemarket = strtoupper(\Utils\Get::get($params, "basemarket", null));




            if (empty($basemarket)) {
                throw new \Exception("Invalid Base Market.");
            }

            if ($storage_marketSummaries->exists('MARKETSUMMARIES_' . $basemarket)) {

                $marketSummaries[] = $storage_marketSummaries->hgetall('MARKETSUMMARIES_' . $basemarket);
                $httpResponse->addBody(null, json_decode($marketSummaries[0][0]));
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);

            } else {

                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridades = $paridadeRn->listar(" id_moeda_trade IN (SELECT m.id FROM moedas m WHERE simbolo = '{$basemarket}') AND status_mercado = 1 AND ativo = 1 AND ativo_api = 1", "ordem ASC");

                if (sizeof($paridades) > 0) {

                    foreach ($paridades as $paridade) {

                        if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
                            $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
                        }

                        $summaries = array(
                            "timestamp" => strtotime(date("Y-m-d H:i:s")),
                            "marketName" => str_replace(":", "_", $paridade->symbol),
                            "marketAsset" => $paridade->moedaBook->simbolo,
                            "baseAsset" => $paridade->moedaTrade->simbolo,
                            "marketAssetName" => $paridade->moedaBook->nome,
                            "baseAssetName" => $paridade->moedaTrade->nome,
                            "high" => number_format($paridade->maiorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                            "low" => number_format($paridade->menorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                            "last" => number_format($paridade->ultimaVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                            "volume" => number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", ""),
                            "baseVolume" => number_format($paridade->moedaTrade->volume, $paridade->moedaTrade->casasDecimais, ".", ""),
                            "bid" => number_format($paridade->precoCompra, $paridade->moedaTrade->casasDecimais, ".", ""),
                            "ask" => number_format($paridade->precoVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                            "isActive" => $paridade->statusMercado == 1 ? true : false,
                            "marketImageData" =>  getenv("EnvUrlProd")  . "/resources/images/currencies/" . $paridade->moedaBook->icone,
                            "baseImageData" => getenv("EnvUrlProd") . "/resources/images/currencies/" . $paridade->moedaTrade->icone,
                            "infoMessage" => "This market can be removed due to the low volume."
                        );



                        $dados[] = $summaries;
                        $summaries = null;

                    };

                    $storage_marketSummaries->hmset('MARKETSUMMARIES_' . $basemarket, array(json_encode($dados)));
                    $storage_marketSummaries->expire('MARKETSUMMARIES_' . $basemarket, 60);

                    if ($storage_marketSummaries->exists('MARKETSUMMARIES_' . $basemarket)) {

                        $marketSummaries[] = $storage_marketSummaries->hgetall('MARKETSUMMARIES_' . $basemarket);
                        $httpResponse->addBody(null, json_decode($marketSummaries[0][0]));
                        $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                    } else {
                        throw new \Exception("Market is not available.", 400);
                    }


                }
            }
        }
            catch
                (\Exception $ex) {
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE404, \Utils\Excecao::mensagem($ex));
                }

        $httpResponse->printResult();

        }

    public function marketSummariesSite($params) {
    $httpResponse = new HttpResult();
    $storage_marketSummariesSite = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));

    try {

        $basemarket = strtoupper(\Utils\Get::get($params, "basemarket", null));




        if (empty($basemarket)) {
            throw new \Exception("Invalid Base Market.");
        }

        if ($storage_marketSummariesSite->exists('MARKETSUMMARIES_SITE' . $basemarket)) {

            $marketSummariesSite[] = $storage_marketSummariesSite->hgetall('MARKETSUMMARIES_SITE' . $basemarket);
            $httpResponse->addBody(null, json_decode($marketSummariesSite[0][0]));
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);

        } else {

            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->listar(" id_moeda_trade IN (SELECT m.id FROM moedas m WHERE simbolo = '{$basemarket}') AND status_mercado = 1 AND ativo = 1 AND ativo_api = 1 AND site = 1", "symbol ASC");

            if (sizeof($paridades) > 0) {

                foreach ($paridades as $paridade) {

                    if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
                        $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
                    }

                    $summaries = array(
                        "timestamp" => strtotime(date("Y-m-d H:i:s")),
                        "marketName" => str_replace(":", "_", $paridade->symbol),
                        "marketAsset" => $paridade->moedaBook->simbolo,
                        "baseAsset" => $paridade->moedaTrade->simbolo,
                        "marketAssetName" => $paridade->moedaBook->nome,
                        "baseAssetName" => $paridade->moedaTrade->nome,
                        "high" => number_format($paridade->maiorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "low" => number_format($paridade->menorPreco, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "last" => number_format($paridade->ultimaVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "volume" => number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", ""),
                        "baseVolume" => number_format($paridade->moedaTrade->volume, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "bid" => number_format($paridade->precoCompra, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "ask" => number_format($paridade->precoVenda, $paridade->moedaTrade->casasDecimais, ".", ""),
                        "isActive" => $paridade->statusMercado == 1 ? true : false,
                        "marketImageData" =>  getenv("EnvUrlProd")  . "/resources/images/currencies/" . $paridade->moedaBook->icone,
                        "baseImageData" => getenv("EnvUrlProd") . "/resources/images/currencies/" . $paridade->moedaTrade->icone,
                        "infoMessage" => "This market can be removed due to the low volume."
                    );



                    $dados[] = $summaries;
                    $summaries = null;

                };

                $storage_marketSummariesSite->hmset('MARKETSUMMARIES_SITE_' . $basemarket, array(json_encode($dados)));
                $storage_marketSummariesSite->expire('MARKETSUMMARIES_SITE_' . $basemarket, 60);

                if ($storage_marketSummariesSite->exists('MARKETSUMMARIES_SITE_' . $basemarket)) {

                    $marketSummariesSite[] = $storage_marketSummariesSite->hgetall('MARKETSUMMARIES_SITE_' . $basemarket);
                    $httpResponse->addBody(null, json_decode($marketSummariesSite[0][0]));
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                } else {
                    throw new \Exception("Market is not available.", 400);
                }


            }
        }
    }
    catch
    (\Exception $ex) {
        $httpResponse->setSuccessful(HTTPResponseCode::$CODE404, \Utils\Excecao::mensagem($ex));
    }

    $httpResponse->printResult();

}};
