<?php

namespace Exchanges;

class Binance {
    
    /**
     * 
     * @param type $market EX: XMRBTC
     * @return type
     * @throws \Exception
     */
    public static function ticker($market) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.binance.com/api/v1/ticker/24hr?symbol=". strtoupper($market),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        }
        
        $object = json_decode($response);
        return Array(
            "high" => (isset($object->highPrice) ? number_format($object->highPrice, 8, ".", "") : 0.00),
            "low" => (isset($object->lowPrice) ? number_format($object->lowPrice, 8, ".", "") : 0.00),
            "volume" => (isset($object->volume) ? number_format($object->volume, 8, ".", "") : 0.00),
            "quantity" =>  (isset($object->ticker->quantity) ? $object->quantity : 0.00),
            "last" => (isset($object->lastPrice) ? number_format($object->lastPrice, 8, ".", "") : 0.00),
            "buy" => (isset($object->bidPrice) ? number_format($object->bidPrice, 8, ".", "") : 0.00),
            "sell" => (isset($object->askPrice) ? number_format($object->askPrice, 8, ".", "") : 0.00)
        );
        
    }
    
}