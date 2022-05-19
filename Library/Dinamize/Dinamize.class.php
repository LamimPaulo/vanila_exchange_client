<?php

/**
 * Description of Dinamize
 *
 * @author willianchiquetto
 */
class Dinamize {
    
    private $email = "willian@cointradecx.com";
    private $senha = "Cointrade15!";
    private $clienteCode = "325239";
    
    public function Auth() {
        
        $token = null;
        
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.dinamize.com/auth",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{    \"user\": \"{$this->email}\",\n    \"password\": \"{$this->senha}\",\n    \"client_code\": \"{$this->clienteCode}\"\n}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Host: api.dinamize.com",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $object = json_decode($response);               
                if($object->code == "480001"){ //Sucesso Auth 
                    
                    $body = $object->body;
                    $dados = (array) $body;                    
                    $token = $dados["auth-token"];
                    
                   return $token;
                    
                } else { //Erro  Auth
                    throw new \Exception("Fail Token.");
                }
            }
        } catch (Exception $ex) {
            throw new \Exception($ex);
        }
        return $token;
    }
    
    public function adcionarCliente(Models\Modules\Cadastro\Cliente $cliente, $token = null) {

        try {
            $cadastro = false;
            
            if (empty($token)) {
                $token = $this->Auth();
            }


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.dinamize.com/emkt/contact/add",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{  \"email\":\"{$cliente->email}\",  \"name\":\"{$cliente->nome}\","
                . "  \"custom_fields\": {       \"cmp4\": [],       \"cmp5\": \"Ativo\",       \"cmp6\": [],"
                        . "       \"cmp7\": \" E-mail confirmado\"},"
                        . "  \"contact-list_code\":\"1\"}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json; charset=utf-8",                    
                    "auth-token: {$token}",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $object = json_decode($response);
                
                if($object->code == "480001"){ 
                   $cadastro = true;
                }
            }
        } catch (Exception $ex) {
            throw new \Exception("Fail Insert Customer. " . $ex);
        }
        
        return $cadastro;
    }
    
    
    public function listarCliente(Models\Modules\Cadastro\Cliente $cliente, $token = null) {

        try {
            
            if (empty($token)) {
                $token = $this->Auth();
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.dinamize.com/emkt/contact/search",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => "{  \"contact-list_code\":\"1\",  \"page_number\":\"1\", \"search\": [{\"field\":\"email\", \"operator\":\"=\", \"value\":\"{$cliente->email}\"}]}",
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json; charset=utf-8",
                "auth-token: {$token}",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

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
    
    public function removerCliente($clienteCode, $token = null) {
        try {

            if (empty($token)) {
                $token = $this->Auth();
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.dinamize.com/emkt/contact/delete",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{  \"contact_code\":\"{$clienteCode}\",  \"contact-list_code\":\"1\"}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json; charset=utf-8",
                    "auth-token: {$token}",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

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
    
    public function atualizarCliente(Models\Modules\Cadastro\Cliente $cliente, $clienteCode, $token = null, $moedas = null) {
        try {

            if (empty($token)) {
                $token = $this->Auth();
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.dinamize.com/emkt/contact/update",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{  \"contact_code\":\"{$clienteCode}\",  \"email\":\"{$cliente->email}\",  \"name\":\"{$cliente->nome}\",  \"custom_fields\": { \"cmp6\": {$moedas}}, \"contact-list_code\":\"1\"}",
                CURLOPT_HTTPHEADER => array(
                  "Content-Type: application/json; charset=utf-8",                  
                  "auth-token: {$token}",
                  "cache-control: no-cache"
                ),
              ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

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
    
    public function listaValores($token = null) {
        try {

            if (empty($token)) {
                $token = $this->Auth();
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.dinamize.com/emkt/field-lov/search",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{  \"contact-list_code\":\"1\",  \"field_code\":\"6\"}",
                CURLOPT_HTTPHEADER => array(
                  "Content-Type: application/json; charset=utf-8",
                  "auth-token: {$token}",   
                  "cache-control: no-cache"
                ),
              ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

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

}
