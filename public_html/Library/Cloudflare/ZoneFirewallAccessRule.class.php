<?php

namespace Cloudflare;

class ZoneFirewallAccessRule {
    
    
    public static function block($ipaddress, $descricao) {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/6db1a6a027237fdc78e7fa30c5f9e7c6/firewall/access_rules/rules",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\"mode\" : \"block\", \"configuration\" : {\"target\": \"ip\", \"value\": \"{$ipaddress}\"}, \"notes\" : \"{$descricao}\"}",
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "X-Auth-Email: renato.oliva@newc.com.br",
            "X-Auth-Key: f69a3b9cfddc701c16f7eebac2b0a67cd24ef",
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        
        $file = fopen("cloudflare-log.txt", 'a');
        fwrite($file, date("d/m/Y H:i:s") . ": " . $response);
        fclose($file);

    }
    
}