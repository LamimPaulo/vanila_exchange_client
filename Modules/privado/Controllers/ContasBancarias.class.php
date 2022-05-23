<?php

namespace Modules\privado\Controllers;

class ContasBancarias {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function salvarContaBancaria($params) {       
        $httpResponse = new HttpResult();

        try {            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());

            if (!empty($cliente)) {
                
                $json = file_get_contents('php://input');            
                $object = json_decode($json);
                
                $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
                $contaBancaria->idBanco = \Utils\SQLInjection::clean($object->bankCode);
                $contaBancaria->conta = \Utils\SQLInjection::clean($object->account);
                $contaBancaria->agencia = \Utils\SQLInjection::clean($object->agency);
                $contaBancaria->tipoConta = \Utils\Constantes::CONTA_CORRENTE;
                $contaBancaria->agenciaDigito = \Utils\SQLInjection::clean($object->agencyDigit);
                $contaBancaria->contaDigito = \Utils\SQLInjection::clean($object->accountDigit);

                $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();

                $contaBancariaRn->salvar($contaBancaria, $cliente);
                
                $contaBancariaRn->carregar($contaBancaria, true, true);

                $httpResponse->addBody("account", $this->montarContar($contaBancaria));
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    public function listarBancos($params) {
        $httpResponse = new HttpResult();

        try {          
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            
            if (!empty($cliente)) {
                $bancoRn = new \Models\Modules\Cadastro\BancoRn();
                $result = $bancoRn->conexao->listar(" ativo = 1 ", "codigo ASC");
                $bancos = Array();
                
                if (sizeof($result) > 0) {
                    foreach ($result as $dados) {
                        $banco = Array(
                            "code" => $dados->codigo,
                            "name" => $dados->nome
                        );

                        $bancos[] = $banco;
                        $banco = null;
                    }
                }

                $httpResponse->addBody("banks", $bancos);

                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    public function getContasCliente($params) {
        
        $httpResponse = new HttpResult();

        try {            
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            $contaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $contas = Array();
            
            if (!empty($cliente)) {                
                $lista = $contaRn->listar("id_cliente = {$cliente->id}", "ativo DESC", null, null, true);
                //$contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
                if (sizeof($lista) > 0) {
                    foreach ($lista as $contaBancaria) {
                        $contas[] = $this->montarContar($contaBancaria);                        
                    }
                }

                $httpResponse->addBody("accounts", $contas);

                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
     
    }
    
    public function ativarDesativarConta($params) {
        
        $httpResponse = new HttpResult();

        try {            
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            $contaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            
            
            if (!empty($cliente)) {

                $json = file_get_contents('php://input');
                $object = json_decode($json);
                
                $idConta = \Utils\Criptografia::decriptyPostId(\Utils\SQLInjection::clean($object->id));
                
                if (empty($idConta)) {
                    throw new \Exception("Request parameter error", 400);
                }

                $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria(Array("id" => $idConta));

                $contaRn->alterarStatusAtivo($contaBancaria);

                $contaRn->carregar($contaBancaria, true, true);

                $httpResponse->addBody(null, $this->montarContar($contaBancaria));

                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
     
    }
    
    private function montarContar(\Models\Modules\Cadastro\ContaBancaria $contaBancaria) {

        $conta = Array(
            "id" => \Utils\Criptografia::encriptyPostId($contaBancaria->id),
            "bankCode" => $contaBancaria->banco->codigo,
            "bankName" => $contaBancaria->banco->nome,
            "ownerName" => $contaBancaria->nomeCliente,
            "document" => $contaBancaria->documentoCliente,
            "agency" => $contaBancaria->agencia,
            "agencyDigit" => $contaBancaria->agenciaDigito,
            "account" => $contaBancaria->conta,
            "accountDigit" => $contaBancaria->contaDigito,
            "typeAccount" => $contaBancaria->tipoConta == \Utils\Constantes::CONTA_CORRENTE ? "Current Account" : "",
            "active" => $contaBancaria->ativo == 1 ? true : false
        );
        
        return $conta;
    }
    
    public function listarBancosExchange($params) {
        
        $httpResponse = new HttpResult();

        try {            
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            $contaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contas = Array();
            
            if (!empty($cliente)) {                
                $lista = $contaRn->listar(" ativo = 1 ", "ativo DESC", null, null, true);
                $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
                
                if (sizeof($lista) > 0) {
                    foreach ($lista as $dados) {
                        
                        $conta = Array(
                            "id" => \Utils\Criptografia::encriptyPostId($dados->id),
                            "bankCode" => $dados->banco->codigo,
                            "bankName" => $dados->banco->nome,
                            "ownerName" => $dados->titular,
                            "document" => $dados->cnpj,
                            "agency" => $dados->agencia,                            
                            "account" => $dados->conta,                            
                            "typeAccount" => $dados->tipoConta == \Utils\Constantes::CONTA_CORRENTE ? "Current Account" : "",
                            "active" => $dados->ativo == 1 ? true : false
                        );
                        
                        $contas[] = $conta;
                        $conta = null;
                    }
                }

                $httpResponse->addBody("accounts", $contas);

                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
     
    }

}