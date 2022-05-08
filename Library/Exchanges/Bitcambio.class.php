<?php

namespace Exchanges;

class Bitcambio {
    
    
    public static function ticker() {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://bitcambio_api.blinktrade.com/api/v1/BRL/ticker",
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
            "high" => (isset($object->high) ? number_format($object->high, 2, ".", "") : 0.00),
            "low" => (isset($object->low) ? number_format($object->low, 2, ".", "") : 0.00),
            "volume" => (isset($object->vol) ? number_format($object->vol, 8, ".", "") : 0.00),
            "last" => (isset($object->last) ? number_format($object->last, 2, ".", "") : 0.00),
            "buy" => (isset($object->buy) ? number_format($object->buy, 2, ".", "") : 0.00),
            "sell" => (isset($object->sell) ? number_format($object->sell, 2, ".", "") : 0.00)
        );
        
    }
    
}