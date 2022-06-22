<?php

namespace Cointrade;

use Exception;

/**
 * Description of Dinamize
 *
 * @author willianchiquetto
 */
class Trade {
    
    public static function comprar($cliente, $symbolParidade, $volume, $preco, $limitada = true) {
        try {
            
            $credenciais = "Basic " . base64_encode($cliente->clientid . ":" . $cliente->apiKey);
            
            $symbolParidade =  str_replace(":", "_", $symbolParidade);
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => Trade::host() . "buy",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>'
                    {
                        "market": "'. $symbolParidade .'",
                        "price": '. $preco . ',
                        "amount": '. $volume . ',
                        "limited": '. $limitada .'
                    }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . $credenciais,
                    'Content-Type: application/json'
                  ),
              ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            curl_close($curl);
            
            if ($err) {
                return null;
            } else {
                return json_decode($response);
            }
        } catch (Exception $ex) {
            return null;
        }
    }
    
    public static function vender($cliente, $symbolParidade, $volume, $preco, $limitada = true) {
        try {
            
            $credenciais = "Basic " . base64_encode($cliente->clientid . ":" . $cliente->apiKey);
            
            $symbolParidade =  str_replace(":", "_", $symbolParidade);
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => Trade::host() . "sell",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>'
                    {
                        "market": "'. $symbolParidade .'",
                        "price": '. $preco . ',
                        "amount": '. $volume . ',
                        "limited": '. $limitada .'
                    }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . $credenciais,
                    'Content-Type: application/json'
                  ),
              ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            curl_close($curl);
            
            if ($err) {
                return null;
            } else {
                return json_decode($response);
            }
        } catch (Exception $ex) {
            return null;
        }
    }
    
    public static function host(){
        
        if(AMBIENTE == "producao"){
            return "https://api.cointradecx.com/private/";
        } else {
            return "http://l.api.cointradecx/private/";
        }
    }

}
