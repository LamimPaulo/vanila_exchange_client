<?php

namespace Utils;

class TickerUtils {
    
    private static $exchanges = Array(
        "BTD", // BitcoinTrade
        "MBT", // Mercado Bitcoin
        "CAM", // Bitcambio,
        "B2Y" // BitcoinToYou
    );
    
    public static function getTickerByExchange($exchange, $moeda, $compraVenda) {
        $preco = 0;
        
        if (strtolower($compraVenda) != "c" && strtolower($compraVenda) != "v") {
            throw new \Exception("Indicador de movimento inválido");
        }
        
        if (!in_array(strtoupper($exchange), self::$exchanges)) {
            throw new \Exception("Exchange não suportada");
        }
        
        switch (strtoupper($exchange)) {
            case "BTD":

                $bitcoinTrade = new \Exchanges\BitcoinTrade();
                $ticker = $bitcoinTrade->ticker();
                
                if (strtolower($compraVenda) == "c") {
                    $preco = $ticker["buy"];
                } else {
                    $preco = $ticker["sell"];
                }
                
                break;
            case "MBT":
                $mercadoBitcoin = new \Exchanges\MercadoBitcoin();
                $ticker = $mercadoBitcoin->ticker();
                
                if (strtolower($compraVenda) == "c") {
                    $preco = $ticker["buy"];
                } else {
                    $preco = $ticker["sell"];
                }
                break;
            case "CAM":
                $ticker = \Exchanges\Bitcambio::ticker();
                
                if (strtolower($compraVenda) == "c") {
                    $preco = $ticker["buy"];
                } else {
                    $preco = $ticker["sell"];
                }
                break;
            case "B2Y":
                $ticker = \Exchanges\BitcointoYou::ticker();
                
                if (strtolower($compraVenda) == "c") {
                    $preco = $ticker["buy"];
                } else {
                    $preco = $ticker["sell"];
                }
                break;

            default:
                throw new \Exception("Exchange não suportada");
        }
        
        return $preco;
    }
    
}