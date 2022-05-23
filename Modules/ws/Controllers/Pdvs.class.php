<?php

namespace Modules\ws\Controllers;

class Pdvs {
    
    public function callbacks($params) {
        $data = new \Utils\Data(date("d/m/Y H:i:s"));
        $data->subtrair(0, 0, 3, 0);
        
        $logCarteiraPdvRn = new \Models\Modules\Cadastro\LogCallbackCarteiraPdvRn();
        $result = $logCarteiraPdvRn->conexao->listar("http_response NOT IN ('200', '202') AND data >= '{$data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}'", "id", null, null);
        
        foreach ($result as $log) {
            
            $curl = curl_init();
        
            curl_setopt_array($curl, array(
                CURLOPT_URL => $log->url,
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

            $result = curl_exec($curl);
            $httpResponse = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $bodyResponse = $result;
            
            $logCarteiraPdvRn->conexao->update(Array("http_response" => $httpResponse, "body_response" => $bodyResponse), Array("id" => $log->id));
            
            curl_close($curl);
            
        }
        
    }
    
}