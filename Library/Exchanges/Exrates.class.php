<?php

namespace Exchanges;

class Exrates {
    
    private static $PUBLIC_KEY = "0SySZKyWnBKu9VFTGPz4ynCaVNXMVSDTsYdRb4Bn";
    private static $PRIVATE_KEY = "Os41IZa6tShmaYpwkjJOK0G5LuARmS27XYXkbT0I";
    
    /**
     * 
     * @param type $market EX: XMRBTC
     * @return type
     * @throws \Exception
     */
    public static function ticker($market) {
        $curl = curl_init();
        
        $time = time();
        
        $string = utf8_encode("GET|/openapi/v1/public/ticker|{$time}|". Exrates::$PUBLIC_KEY);
        $hash = hash_hmac('sha256', $string, utf8_encode(Exrates::$PRIVATE_KEY));
        $sig = \Utils\Conversao::strToHex($hash);
        $sig = utf8_decode($sig);
        //$sig = $hash;
        
        echo " String: {$string} <br><br>";
        echo " HASH: {$hash} <br><br>";
        echo " HEX: {$sig} <br><br>";

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://exrates.me/openapi/v1/public/ticker?currency_pair=". strtoupper($market),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "API-KEY: " . Exrates::$PUBLIC_KEY,
            "API-TIME: {$time}",
            "API-SIGN: {$sig}",
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        exit($response);
        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        }
        exit($response);
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