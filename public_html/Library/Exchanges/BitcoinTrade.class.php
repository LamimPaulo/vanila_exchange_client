<?php

namespace Exchanges;

class BitcoinTrade {
    
    public static function ticker() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.bitcointrade.com.br/v1/public/BTC/ticker",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "postman-token: 56ed55c6-19b9-2afd-461e-46cce74928a6"
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
            "high" => (isset($object->data->high) ? number_format($object->data->high, 2, ".", "") : 0.00),
            "low" => (isset($object->data->low) ? number_format($object->data->low, 2, ".", "") : 0.00),
            "volume" => (isset($object->data->volume) ? number_format($object->data->volume, 8, ".", "") : 0.00),
            "quantity" =>  (isset($object->data->quantity) ? $object->data->quantity : 0.00),
            "last" => (isset($object->data->last) ? number_format($object->data->last, 2, ".", "") : 0.00),
            "buy" => (isset($object->data->buy) ? number_format($object->data->buy, 2, ".", "") : 0.00),
            "sell" => (isset($object->data->sell) ? number_format($object->data->sell, 2, ".", "") : 0.00)
        );
        
    }
    
}