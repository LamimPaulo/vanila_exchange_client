<?php

namespace Correios;

class CEP {

    public static function buscar($cep) {


        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://viacep.com.br/ws/{$cep}/json",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));


        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        if ($err) {
            return null;   
            
        } else {
            if($httpcode == 200){
                return json_decode($response);
            } else {
                return null;
            }
        }
    }

}
