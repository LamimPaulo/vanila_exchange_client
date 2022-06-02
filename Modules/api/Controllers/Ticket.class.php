<?php

namespace Modules\api\Controllers;

class Ticket {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function markets($params) {
        try {
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->listar("ativo = 1", null, null, NULL, FALSE, FALSE);
            
            $lista = Array();
            foreach ($paridades as $paridade) {
                $lista[] = "{$paridade->symbol}";
            }
            
            $json["markets"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
//    public function market($params) {
//        try {
//            
//            $market = \Utils\Get::get($params, 0, NULL);
//            
//            if (empty($market)) {
//                throw new \Exception("Mercado inválido!");
//            }
//            
//            if (!is_numeric(strpos($market, ":"))) {
//                $market = "{$market}:BRL";
//            }
//            
//            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
//            $paridade = $paridadeRn->getBySymbol($market);
//            
//            if ($paridade == null) {
//                throw new \Exception("Mercado inválido!");
//            }
//            
//            $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
//            $dataInicial->subtrair(0, 0, 1);
//            $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
//            
//            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
//            
//            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
//            $volume = $ordemExecutadaRn->calcularVolumeParidade($paridade->id, $dataInicial, $dataFinal);
//            
//            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
//            $precos = $orderBookRn->getPrecos($paridade->id);
//            $maiorMenor = $orderBookRn->getPrecoMinMaxDia($dataFinal, $paridade);
//            
//            $casasDeciamais = ($paridade->idMoedaTrade == 1 ? $configuracao->qtdCasasDecimais : $paridade->moedaTrade->casasDecimais);
//            
//            $json["market"] = Array(
//                "currency" => "{$paridade->symbol}",
//                "buyPrice" => number_format($precos["compra"], $casasDeciamais, ".", ""),
//                "sellPrice" => number_format($precos["venda"], $casasDeciamais, ".", ""),
//                "lowPrice" => number_format($maiorMenor["min"], $casasDeciamais, ".", ""),
//                "lastPrice" => number_format($precos["ultimo"], $casasDeciamais, ".", ""),
//                "highPrice" => number_format($maiorMenor["max"], $casasDeciamais, ".", ""),
//                "volumeCurrency" => number_format($volume["currency"], $paridade->moedaBook->casasDecimais, ".", ""),
//                "volume{$paridade->idMoedaTrade->simbolo}" => number_format($volume["reais"], $casasDeciamais, ".", "")
//            );
//                
//            $json["sucesso"] = true;
//        } catch (\Exception $ex) {
//            $json["sucesso"] = false;
//            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
//        }
//        print json_encode($json);
//    }
}