<?php

namespace Exchanges;

class Bittrex {
    
    /**
     * 
     * @param type $market EX: BTC-USD
     * @return type
     * @throws \Exception
     */
    public static function ticker($market) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://bittrex.com/api/v1.1/ticker/public/getticker?market=". strtoupper($market),
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
            "high" => (isset($object->high) ? number_format($object->high, 8, ".", "") : 0.00),
            "low" => (isset($object->low) ? number_format($object->low, 8, ".", "") : 0.00),
            "volume" => (isset($object->volume) ? number_format($object->volume, 8, ".", "") : 0.00),
            "quantity" =>  (isset($object->quantity) ? $object->quantity : 0.00),
            "last" => (isset($object->Last) ? number_format($object->Last, 8, ".", "") : 0.00),
            "buy" => (isset($object->Bid) ? number_format($object->Bid, 8, ".", "") : 0.00),
            "sell" => (isset($object->Ask) ? number_format($object->Ask, 8, ".", "") : 0.00)
        );
        
    }
    
}