<?php

namespace Exchanges;

class BitcointoYou {
    
    public static function ticker() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.bitcointoyou.com/API/ticker.aspx",
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
            "high" => (isset($object->ticker->high) ? number_format($object->ticker->high, 2, ".", "") : 0.00),
            "low" => (isset($object->ticker->low) ? number_format($object->ticker->low, 2, ".", "") : 0.00),
            "volume" => (isset($object->ticker->vol) ? number_format($object->ticker->vol, 8, ".", "") : 0.00),
            "quantity" =>  (isset($object->ticker->quantity) ? $object->ticker->quantity : 0.00),
            "last" => (isset($object->ticker->last) ? number_format($object->ticker->last, 2, ".", "") : 0.00),
            "buy" => (isset($object->ticker->buy) ? number_format($object->ticker->buy, 2, ".", "") : 0.00),
            "sell" => (isset($object->ticker->sell) ? number_format($object->ticker->sell, 2, ".", "") : 0.00)
        );
        
    }
    
}