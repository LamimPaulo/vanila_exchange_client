<?php

namespace Exchanges;

class Coinex {
    
    /**
     * 
     * @param type $market EX: BTCBCH
     * @return type
     * @throws \Exception
     */
    public static function ticker($market) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.coinex.com/v1/market/ticker?market=". strtoupper($market),
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
            "high" => (isset($object->data->ticker->high) ? number_format($object->data->ticker->high, 8, ".", "") : 0.00),
            "low" => (isset($object->data->ticker->low) ? number_format($object->data->ticker->low, 8, ".", "") : 0.00),
            "volume" => (isset($object->data->ticker->vol) ? number_format($object->data->ticker->vol, 8, ".", "") : 0.00),
            "quantity" =>  (isset($object->data->ticker->quantity) ? $object->data->ticker->quantity : 0.00),
            "last" => (isset($object->data->ticker->last) ? number_format($object->data->ticker->last, 8, ".", "") : 0.00),
            "buy" => (isset($object->data->ticker->buy) ? number_format($object->data->ticker->buy, 8, ".", "") : 0.00),
            "sell" => (isset($object->data->ticker->sell) ? number_format($object->data->ticker->sell, 8, ".", "") : 0.00)
        );
        
    }
    
}