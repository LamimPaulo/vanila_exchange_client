<?php

namespace Atar;

class AtarApi {
    
    protected $host;
    protected $user;
    protected $password;
    protected $credenciais;
    
    public function __construct() {
        $this->user = getenv("EnvAtarUser");
        $this->password = getenv("EnvAtarPassword");
        
        if(AMBIENTE == "producao"){
            $this->host = getenv("EnvAtarUrlProd");
        } else {
            $this->host = getenv("EnvAtarUrlDev");
        }
        
        
        
        $this->credenciais = "Basic " . base64_encode($this->user . ":" . $this->password);
    }

    public function consultarSaldo() {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->host . "balance",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: {$this->credenciais}",
                "Cache-Control: no-cache",
                "Connection: keep-alive"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->salvarLog($err);
            return null;   
        } else {
            $this->salvarLog($response);
            return json_decode($response);
        }
    }

    public function extrato(\Utils\Data $dataInicio = null, \Utils\Data $dataFim = null, $cursor = null) {        
        
        if(empty($dataInicio)){
            throw new \Exception("Por favor, inserir uma data de ínicio válida."); 
        }
        
        if(empty($dataFim)){
            throw new \Exception("Por favor, inserir uma data de fim válida."); 
        }
        
        if($dataInicio->maior($dataFim)){
            throw new \Exception("A data fim deve ser maior que a data de ínicio."); 
        }
        
        
        $timestampInicio = strtotime($dataInicio->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO));
        $timestampFim = strtotime($dataFim->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO));
        
        if($cursor != null){
            "&cursor={$cursor}";
        } else {
            $whereCursor = "";
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->host . "statements?start={$timestampInicio}&end={$timestampFim}{$whereCursor}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(                
                "Authorization: {$this->credenciais}",
                "Cache-Control: no-cache",
                "Connection: keep-alive"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);

        if ($err) {
            $this->salvarLog($err);
            return null;            
        } else {
            return json_decode($response);
        }
        
        
    }

    public function transferenciaInterna($valor = null, $documentoCpf = null) {

        if(empty($valor) || $valor <= 0 ){
             throw new \Exception("Por favor, inserir um valor válido.");            
        }
        
        if(empty($documentoCpf)){
             throw new \Exception("Por favor, inserir um documento válido.");            
        }
        
        //Converte o valor para centavos, precisa enviar um numero inteiro
        $valorInteger = $valor * 100;
        
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->host . "internal-transfers",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n    \"amount\": {$valorInteger},\n    \"document\": \"{$documentoCpf}\"\n}",
            CURLOPT_HTTPHEADER => array(                
                "Authorization: {$this->credenciais}",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        if ($err) {
            $this->salvarLog($err . " - " . $httpStatus);
            return $httpStatus;
        } else {
            $this->salvarLog($response . " - " . $httpStatus);
            return json_decode($response);
        }
    }
    
    public function consultarTransferencia($idTransferencia = null) {

        if(empty($idTransferencia)){
             throw new \Exception("Por favor, inserir um ID de transferência válido.");            
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->host . "internal-transfers/{$idTransferencia}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: {$this->credenciais}",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->salvarLog($err);
            return null;
        } else {
            $this->salvarLog($response);
            return json_decode($response);
        }
    }
    
    private function salvarLog($response){
        $atarLog = new \Models\Modules\Cadastro\AtarLog();
        $atarLogRn = new \Models\Modules\Cadastro\AtarLogRn();
        
        $atarLog->response = $response;
        $atarLogRn->salvar($atarLog);        
    }

}
