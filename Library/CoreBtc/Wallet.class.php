<?php

namespace CoreBtc;

class Wallet {
    
    public static function create($name) {
        
        if (empty($name)) {
            throw new \Exception("É necessário informar o nome da carteira");
        }
        
        $params = Array(
            "method" => "getaccountaddress",
            "params" => Array(
                $name
            )
        );
        
        $curl = curl_init();

        $authorization = Credentials::getAuthorization();
        
        curl_setopt_array($curl, array(
          CURLOPT_PORT => Credentials::SERVER_PORT,
          CURLOPT_URL => Credentials::getHost(),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($params),
          CURLOPT_HTTPHEADER => array(
            "authorization: Basic {$authorization}",
            "cache-control: no-cache",
            "content-type: application/json-rpc"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        } 
        
        $json = json_decode($response, false);
        //exit(print_r($json));
        if ($json->error != null) {
            throw new \Exception("Houve uma falha na comunicação com o serviço. Por favor tente novamente mais tarde.");
        }
        
        return $json->result;
    }
    
    
    
    public static function get($address) {
        if (empty($address)) {
            throw new \Exception("É necessário informar o endereço da carteira");
        }
        
        $params = Array(
            "method" => "listunspent",
            "params" => Array(
                0,
                9999999,
                Array(
                    $address
                )
            )
        );
        
        $curl = curl_init();

        $authorization = Credentials::getAuthorization();
        
        curl_setopt_array($curl, array(
          CURLOPT_PORT => Credentials::SERVER_PORT,
          CURLOPT_URL => Credentials::getHost(),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($params),
          CURLOPT_HTTPHEADER => array(
            "authorization: Basic {$authorization}",
            "cache-control: no-cache",
            "content-type: application/json-rpc"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        } 
        
        $json = json_decode($response, false);
        //exit(print_r($json));
        if ($json->error != null) {
            throw new \Exception($json->error);
        }
        //exit(print_r($json));
        return $json->result;
    }
    
}