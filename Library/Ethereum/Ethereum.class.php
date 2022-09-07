<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Utils;

/**
 * Description of Ethereum
 *
 * @author willianchiquetto
 */
class Ethereum {

    public $api_key = "d153d75a4f4f24aef0f8e07039e29111a70f7eee";
    private $api_key_etherscan = "SKTIQ5X898TWXXN8G59Q19HFHMH97NC8C3";
    private $api_key_etherscan_2 = "MKG1HAQ9HXE1C1YTEG9XBVPX42GF5VG7GH"; 
    private $redeERC20 = "https://api.etherscan.io/";
    
    private $redeBEP20 = "https://api.bscscan.com/";
    private $api_key_bscscan = "SNCVYZ84E1FENX6GMFWI26P6T24ICMH78T";
    
    protected $rede = "";
    protected $key = "";
            
    
    function __construct($rede = null) {

        switch ($rede){
            case Constantes::REDE_ERC20:
                $this->rede = $this->redeERC20;
                $this->key = $this->api_key_etherscan;
                break;
            case Constantes::REDE_BEP20:
                //Banco de Dados Somente Leitura - Book
                $this->rede = $this->redeBEP20;
                $this->key = $this->api_key_bscscan;
                break;
            default:
                $this->rede = $this->redeERC20;
                $this->key = $this->api_key_etherscan;
                break;
        }
    }

    public function getBalanceErc20($contrato = null, $endereco = null, $action = "tokentx") {

        //    $numero = random_int(0, 9);
        //    if($numero % 2 == 0){
        //        $key = $this->api_key_etherscan_2;
        //    } else {
        //        $key = $this->api_key_etherscan;
        //    }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->rede . "api?module=account&action={$action}"
            . "&contractaddress={$contrato}"
            . "&address={$endereco}&page=1&offset=1000&sort=desc"
            . "&apikey={$this->key}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        $info = curl_getinfo($curl);
        
        curl_close($curl);
        
        if ($err) {
            $this->salvarLog($endereco, $err);
            return null;
        } else {
            //$this->salvarLog($endereco . " - " . $contrato, $response);
            return $response;
        }
    }

    public function getBalanceEthereum($endereco = null, $action = "txlist") {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->rede. "api?module=account&action={$action}"
            . "&address={$endereco}&startblock=0&endblock=99999999&sort=asc"
            . "&apikey={$this->key}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->salvarLog("", $err);
            return null;
        } else {
            //$this->salvarLog($endereco, "");
            return $response;
        }
    }

    public function getInformationToken($contrato = null){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ethplorer.io/getTokenInfo/{$contrato}?apiKey=freekey",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",                
                "Connection: keep-alive",
                "Host: api.ethplorer.io",                
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    /*
    public function subscribeAddress(\Models\Modules\Cadastro\Moeda $moeda, $carteira) {

        $url = URLBASE_CLIENT . "ws/erc20/" . $carteira;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://eu.api.tokengateway.io/subscribeAddress",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{\"apikey\": \"{$this->api_key}\",\"ethereumaddress\": \"{$carteira}\","
            . " \"contractaddress\": \"{$moeda->contrato}\", \"url\": \"{$url}\"}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        
        if ($err) {
            $this->salvarLog($carteira, $err);
            return false;
        } else {
            $this->salvarLog($carteira, $response);
            $object = json_decode($response);

            if ($object->ok == true) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function unsubscribeAddress(\Models\Modules\Cadastro\Moeda $moeda, $carteira) {

        $url = URLBASE_CLIENT . "ws/erc20/" . $carteira;
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://eu.api.tokengateway.io/UnsubscribeAddress",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{\"apikey\": \"{$this->api_key}\",\"ethereumaddress\": \"{$carteira}\","
            . " \"contractaddress\": \"{$moeda->contrato}\", \"url\": \"{$url}\"}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->salvarLog($carteira, $err);
            return false;
        } else {
            $this->salvarLog($carteira, $response);
            return true;
        }
    }

    public function listSubscribedAddresses() {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://eu.api.tokengateway.io/listSubscribedAddresses",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{\"apikey\": \"{$this->api_key}\"}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->salvarLog("", $err);
            return false;
        } else {
            $this->salvarLog("", $response);
            return true;
        }
    }
    */
    
    
    public function salvarLog($carteira, $response){
        $tokenGatewayLogRn = new \Models\Modules\Cadastro\TokenGatewayLogRn();
        $tokenGatewayLog = new \Models\Modules\Cadastro\TokenGatewayLog();
        
        $tokenGatewayLog->endereco = $carteira;
        $tokenGatewayLog->response = $response;
        
        $tokenGatewayLogRn->salvar($tokenGatewayLog);
    }

}
