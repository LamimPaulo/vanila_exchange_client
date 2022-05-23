<?php

namespace Modules\privado\Controllers;

class Withdraw {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }   
    
    public function listarSaquesBrl($params) {
        
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
                
                $dataInicial = \Utils\SQLInjection::clean($object->startDate);
                $dataFinal = \Utils\SQLInjection::clean($object->endDate);
                $idContaBancaria = \Utils\Criptografia::decriptyPostId($object->account, true);               
                $status = \Utils\SQLInjection::clean($object->status);              
                $nresultado = \Utils\SQLInjection::clean($object->depth);
                
                $saquesView = Array();
                
                if(empty($idContaBancaria)){
                    $idContaBancaria = null;
                }
                
                if(empty($status)){
                    $status = "T";
                }
                               
                if(empty($nresultado)){
                    $nresultado = "T";
                }
                                               
                if(!empty($dataInicial) && is_numeric($dataInicial)){
                    $dataInicial = new \Utils\Data(date("Y-m-d H:i:s", $dataInicial));
                } else {
                    throw new \Exception("Request parameter error - " . "startDate", 400);
                }
                
                if(!empty($dataFinal) && is_numeric($dataFinal)){
                    $dataFinal = new \Utils\Data(date("Y-m-d H:i:s", $dataFinal));
                } else {
                    throw new \Exception("Request parameter error - " . "endDate", 400);
                }
                
                $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
                $saques = $saqueRn->filtrar($cliente->id, $dataInicial, $dataFinal, $idContaBancaria, $status, null, $nresultado);
                //$saque = new \Models\Modules\Cadastro\Saque();
                if(sizeof($saques) > 0){
                    foreach ($saques as $saque){
                        $saquesView[] = $this->montarSaque($saque);
                    }
                     $httpResponse->addBody("withdraws", $saquesView);
                }

                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    
    public function listarSaquesCrypto($params) {

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
                
                $asset = \Utils\SQLInjection::clean($object->asset);
                $dataInicial = \Utils\SQLInjection::clean($object->startDate);
                $dataFinal = \Utils\SQLInjection::clean($object->endDate);                          
                $nresultado = \Utils\SQLInjection::clean($object->depth);
                
                $saquesView = Array();
                
                if(empty($asset)){
                    throw new \Exception("Request parameter error - " . "asset", 400);
                }
                
                if(empty($nresultado)){
                    $nresultado = "T";
                }
                                               
                if(!empty($dataInicial) && is_numeric($dataInicial)){
                    $dataInicial = new \Utils\Data(date("Y-m-d H:i:s", $dataInicial));
                } else {
                    throw new \Exception("Request parameter error - " . "startDate", 400);
                }
                
                if(!empty($dataFinal) && is_numeric($dataFinal)){
                    $dataFinal = new \Utils\Data(date("Y-m-d H:i:s", $dataFinal));
                } else {
                    throw new \Exception("Request parameter error - " . "endDate", 400);
                }
                
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moeda = $moedaRn->getBySimbolo($asset);
                
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $saques = $contaCorrenteBtcRn->filtrar($cliente->id, $dataInicial, $dataFinal, \Utils\Constantes::SAIDA, null, "T", $moeda->id, $nresultado, false);
                
                $saques = $saques['lista'];
                
                if(sizeof($saques) > 0){
                    foreach ($saques as $contaCorrenteBtc){
                        $contaCorrenteBtcRn->carregar($contaCorrenteBtc);
                        $dados = Array(
                            "id" => $contaCorrenteBtc->id,
                            "asset" => $moeda->simbolo,
                            "date" => $contaCorrenteBtc->dataCadastro != null ? strtotime($contaCorrenteBtc->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)) : null,
                            "amount" => $contaCorrenteBtc->valor,
                            "receipt" => empty($contaCorrenteBtc->hash) ? "" : $contaCorrenteBtc->hash,
                            "address" => empty($contaCorrenteBtc->enderecoBitcoin) ? "" : $contaCorrenteBtc->enderecoBitcoin,
                            "transfertType" => $contaCorrenteBtc->direcao == \Utils\Constantes::TRANF_EXTERNA ? "External Transfer" : "Internal Transfer",
                        );
                        
                        $saquesView[] = $dados;
                        $dados = null;
                    }
                     $httpResponse->addBody("withdraws", $saquesView);
                }

                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    
    public function solicitarSaque($params) {

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
                
                $saque = new \Models\Modules\Cadastro\Saque();
                
                $saque->cliente = $cliente;
                
                $saque->idContaBancaria = \Utils\SQLInjection::clean(\Utils\Criptografia::decriptyPostId($object->idBankAccount));
                $saque->valorSaque = \Utils\SQLInjection::clean($object->value);
               
                $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
                
                if (!is_numeric($saque->valorSaque) || $saque->valorSaque < 0) {
                    throw new \Exception("Incorrect value", 400);
                }
                
                if (!is_numeric($saque->idContaBancaria) || $saque->idContaBancaria < 0) {
                    throw new \Exception("Incorrect bank account", 400);
                }
                
                if ($cliente->documentoVerificado != 1) {
                    throw new \Exception("Por favor, informe seu CPF no menu Meu Pefil, aba Meus Dados.", 400);
                }

                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente);

                if ($saldo < $saque->valorSaque) {
                    throw new \Exception($this->idioma->getText("saldoInsuficiente"), 400);
                }

                $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
                $contaBancaria->id = $saque->idContaBancaria;

                try {
                    $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
                    $contaBancariaRn->carregar($contaBancaria, true, true);
                } catch (\Exception $ex) {
                    throw new \Exception($this->idioma->getText("contaNaoEncontradaC"), 400);
                }

                $comissao = 0;
                if ($cliente->considerarTaxaSaqueCliente) {
                    $comissao = $cliente->taxaComissaoSaque;
                } else {
                    $comissao = $configuracao->taxaSaque;
                }
                
                $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
                $saque = $saqueRn->solicitarSaque($saque);
                        
                $saque->contaBancaria = $contaBancaria;
                
                $withdraws = Array();
                $withdraws[] = $this->montarSaque($saque);
                
                $httpResponse->addBody("withdraws", $withdraws);
               
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            }
            
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    
    private function montarSaque(\Models\Modules\Cadastro\Saque &$saque) {
        
        $dados = Array(
            "id" => $saque->id,
            "date" => strtotime($saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
            "account" => Array(
                "code" => $saque->contaBancaria->banco->codigo,
                "name" => $saque->contaBancaria->banco->nome,
                "agency" => $saque->contaBancaria->agencia . "-" . $saque->contaBancaria->agenciaDigito,
                "account" => $saque->contaBancaria->conta . "-" . $saque->contaBancaria->contaDigito,
            ),
            "amount" => number_format($saque->valorSaque, 2, ".", ""),
            "feePorcent" => number_format($saque->taxaComissao, 2, ".", ""),
            "feeBrl" => number_format($saque->valorComissao, 2, ".", ""),
            "tedFee" => number_format($saque->tarifaTed, 2, ".", ""),
            "withdrawCredited" => number_format($saque->valorSacado, 2, ".", ","),
            "status" => $saque->status
        );
        
        return $dados;
    }

}