<?php

namespace Modules\api\Controllers;

class Calc {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
//    public function buy($params) {
//        try {
//            
//            $from = \Utils\Post::get($params, "from",  "");
//            $to = \Utils\Post::get($params, "to",  "");
//            $value = \Utils\Post::get($params, "amount", 0);
//            
//            if (!$value > 0) {
//                throw new \Exception("O valor deve ser maior que zero");
//            }
//            
//            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
//            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
//            $configuracaoRn->conexao->carregar($configuracao);
//            
//            if ($to == "BRL") {
//                
//                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
//                $moeda = $moedaRn->getBySimbolo($from);
//                
//                $coinMarketCap = $this->getCotacaoFromCoinMarketCap($moeda->nome);
//                
//                $real = $value * number_format($coinMarketCap->price_brl, "8", ".", "");
//                
//                if ($configuracao->percentualCompra > 0) { 
//                    $real += ($real * ($configuracao->percentualCompra / 100));
//                }
//                
//                $json["BRL"] = number_format($real, 2, ",", "");
//                
//            } else {
//                 
//                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
//                $moeda = $moedaRn->getBySimbolo($to);
//                
//                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
//                $paridade = $paridadeRn->getBySymbol("{$moeda->simbolo}:BRL");
//                
//                $preco = 0;
//                
//                if ($moeda->statusMercado > 0) { 
//                    $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
//                    $p = $orderBookRn->getPrecos($paridade->id);
//                    $preco = $p["venda"];
//                    //exit(print_r($p));
//                } else {
//                    $coinMarketCap = $this->getCotacaoFromCoinMarketCap($moeda->nome);
//                    $preco = number_format($coinMarketCap->price_brl, $moeda->casasDecimais, ".", "");
//                }
//                
//                if ($configuracao->percentualCompra > 0) {
//                    $value -= ($value * ($configuracao->percentualCompra / 100));
//                }
//                
//                $btc = $value / $preco;
//                
//                $json[$moeda->simbolo] = number_format($btc, $moeda->casasDecimais, ".", "");
//            } 
//            
//            
//            $codigo = strtolower($moeda->nome);
//            $json["fontenome"] = "CoinMarketCap";
//            $json["fonteurl"] = "https://coinmarketcap.com/currencies/{$codigo}";
//            $json["sucesso"] = true;
//        } catch (\Exception $ex) {
//            $json["sucesso"] = false;
//            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
//        }
//        print json_encode($json);
//    }
    
    
    private function getCotacaoFromCoinMarketCap($moeda) {
        $moeda = strtolower($moeda);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.coinmarketcap.com/v1/ticker/{$moeda}/?convert=BRL",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "postman-token: 6a3a0a09-eb19-565d-ef2a-e6d2c928da6a"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        }
        
        $json = json_decode($response, false);
        
        if (isset($json->error)) {
            throw new \Exception($json->error);
        }
        
        return $json[0];
    }
    
}