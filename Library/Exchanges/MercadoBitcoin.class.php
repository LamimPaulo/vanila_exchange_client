<?php

namespace Exchanges;

class MercadoBitcoin {
    
    public static function ticker() {
        $response = file_get_contents("https://www.mercadobitcoin.net/api/BTC/ticker/");
        
        $object = json_decode($response);
        $dados =Array(
            "high" => (isset($object->ticker->high) ? number_format($object->ticker->high, 8, ".", "") : 0.00000000),
            "low" => (isset($object->ticker->low) ? number_format($object->ticker->low, 8, ".", "") : 0.00000000),
            "vol" => (isset($object->ticker->vol) ? number_format($object->ticker->vol, 8, ".", "") : 0.00000000),
            "last" => (isset($object->ticker->last) ? number_format($object->ticker->last, 8, ".", "") : 0.00000000),
            "buy" => (isset($object->ticker->buy) ? number_format($object->ticker->buy, 8, ".", "") : 0.00000000),
            "sell" => (isset($object->ticker->sell) ? number_format($object->ticker->sell, 8, ".", "") : 0.00000000)
        ); 
        return $dados;
    }
}