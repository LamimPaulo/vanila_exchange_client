<?php

namespace Exchanges;

class Bithumb {
    
    /**
     * 
     * @param type $market EX: BTC
     * @return type
     * @throws \Exception
     */
    public static function ticker($market) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.bithumb.com/public/ticker/". strtoupper($market),
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
            "high" => (isset($object->data->high) ? number_format($object->data->high, 8, ".", "") : 0.00),
            "low" => (isset($object->data->low) ? number_format($object->data->low, 8, ".", "") : 0.00),
            "volume" => (isset($object->data->volume) ? number_format($object->data->volume, 8, ".", "") : 0.00),
            "quantity" =>  (isset($object->data->quantity) ? $object->data->quantity : 0.00),
            "last" => (isset($object->data->last_price) ? number_format($object->data->last_price, 8, ".", "") : 0.00),
            "buy" => (isset($object->data->bid) ? number_format($object->data->bid, 8, ".", "") : 0.00),
            "sell" => (isset($object->data->ask) ? number_format($object->data->ask, 8, ".", "") : 0.00)
        );
        
    }
    
}