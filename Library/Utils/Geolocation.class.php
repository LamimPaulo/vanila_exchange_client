<?php

namespace Utils;



class Geolocation {
    
    private static $ACCESS_KEY = "bf00ae2eb20ad41c7048499b4253a7a9";
    
    public static function locate($ipaddress) {
        
        
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://api.ipstack.com/{$ipaddress}?access_key=" . self::$ACCESS_KEY,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        

        if ($err) {
            throw new \Exception($err);
        } 
        
        $obj = json_decode($response);
        
        curl_close($curl);
        
        return $obj;
    
    }
    
    
}