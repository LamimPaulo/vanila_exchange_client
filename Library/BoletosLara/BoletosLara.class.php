<?php

namespace BoletosLara;

class BoletosLara {
    
    protected $host;
    protected $user;
    protected $password;
    protected $token;
    protected $credenciais;
    protected $idEmpresa;
    
    public function __construct() {
        $this->user = getenv("EnvLaraUser");
        $this->password = getenv("EnvLaraPassword");
        $this->token = getenv("EnvLaraToken");
        $this->idEmpresa = getenv("EnvLaraIdEmpresa");
        
        if(AMBIENTE == "producao"){
            $this->host = getenv("EnvLaraUrlProd");
        } else {
            $this->host = getenv("EnvLaraUrlDev");
        }
        
        $this->credenciais = "Basic " . base64_encode($this->user . ":" . $this->password);
    }

    public function gerarBoleto(\Models\Modules\Cadastro\Deposito &$deposito) {

        $object = null;
        
        //Dados do cliente
        //Caso tenha o idLara do cliente, não precisa preencher todos os campos novamente.
        if(!empty($deposito->cliente->parceiroLara)){
            $object->identificacao->parceiroUid = $deposito->cliente->parceiroLara;
            
        } else {
            
            
            $object->identificacao2->parceiroUid = "";
            
            //Dados cliente
            $cep = \Utils\Validacao::limparString($deposito->cliente->cep, true);
            
            $object->pagador->documento = $deposito->cliente->documento;
            $object->pagador->dtNascimento = $deposito->cliente->dataNascimento->formatar(\Utils\Data::FORMATO_ISO);
            $object->pagador->nome = $deposito->cliente->nome;
            $object->pagador->cep = $cep;
            $object->pagador->endereco =  $deposito->cliente->endereco;
            $object->pagador->enderecoN =  $deposito->cliente->numero;
            $object->pagador->complemento = $deposito->cliente->complemento;
            $object->pagador->bairro =  $deposito->cliente->bairro;
            
            if(empty($cep)){
                return null;
            } else {
                if(empty($deposito->cliente->cidade)){
                    $dados = \Correios\CEP::buscar($cep);
                    
                    if(!empty($dados)){
                        $object->pagador->cidade = strtoupper($dados->localidade); 
                        $object->pagador->estado = $dados->uf; 
                        $object->pagador->pais =  "Brasil"; 
                    }
                } else {
                    $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
                    $cidade = new \Models\Modules\Cadastro\Cidade();
                    
                    $cidade->codigo = $deposito->cliente->cidade;
                    
                    $cidadeRn->carregar($cidade, true, true);
                    
                    $object->pagador->cidade = $cidade->nome;
                    $object->pagador->estado =  $cidade->estado->sigla;
                    $object->pagador->pais =  "Brasil"; 
                }
            }
            
        }
        
        //Dados da operação
        $object->dadosOperacao->valor = floatval(number_format($deposito->valorDepositado, 2));
        $object->dadosOperacao->vencimento = $deposito->dataVencimentoGateway->formatar(\Utils\Data::FORMATO_ISO);
        $object->dadosOperacao->juros = 0;
        $object->dadosOperacao->mora = 0; 
        $object->dadosOperacao->desconto = 0;

        
        //Notificações Empresa
        $object->dadosConf->campainha = "https://broker.cointradecx.com/ws/laraboletos/campainha"; //Campainha para Lara
        $object->dadosConf->identInterno = $deposito->id;
        
        //Notificações Cliente
        $celularCliente = "55" . \Utils\Validacao::limparString($deposito->cliente->celular);
        
        $object->dadosSend->wpp = $celularCliente;
        $object->dadosSend->email = $deposito->cliente->email;
        $object->dadosSend->sms = $celularCliente;
      
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->host}{$this->idEmpresa}/fin/boleto/add",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($object),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->token}",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        if ($err) {
            $this->salvarLog($err, "Cadastrar Boleto");
            return null;   
            
        } else {
             $this->salvarLog($response, "Cadastrar Boleto");
             
            if($httpcode == 200){
                $objectReturn = json_decode($response);
                
                if($objectReturn->situacao == "suc"){
                    return $objectReturn->protocolo;
                    
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }
    
    public function consultarBoleto($boleto) {
        
        $novoObject = null;
        
        $novoObject->dadosConf->identInterno = $boleto->dadosConf->identInterno;
        
        //exit(print_r($boleto));
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $boleto->EndPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $boleto->method,
            CURLOPT_POSTFIELDS => json_encode($novoObject),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$this->token}",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if ($err) {
            $this->salvarLog($err, "Consultar Boleto");
            return null;   
            
        } else {
             $this->salvarLog($response, "Consultar Boleto");
             
            if($httpcode == 200){
                $objectReturn = json_decode($response);
                
                if($objectReturn->situacao == "suc"){
                    return $objectReturn;
                    
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }
    
    private function salvarLog($response, $categoria = "Boleto"){
        $laraLog = new \Models\Modules\Cadastro\LaraLog();
        $laraLogRn = new \Models\Modules\Cadastro\LaraLogRn();
        
        $laraLog->categoria = $categoria;
        $laraLog->response = $response;
        $laraLogRn->salvar($laraLog);        
    }

}
