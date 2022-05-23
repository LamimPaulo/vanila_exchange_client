<?php

namespace Modules\privado\Controllers;

class Deposit {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }   
    
    public function listarDepositosBrl($params) {
        
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
                $idContaBancariaEmpresa = \Utils\Criptografia::decriptyPostId($object->exchangeAccount, true);               
                $status = \Utils\SQLInjection::clean($object->status);
                $tipoDeposito = \Utils\SQLInjection::clean($object->depositType);                
                $nresultado = \Utils\SQLInjection::clean($object->depth);
                
                $depositosView = Array();
                
                if(empty($idContaBancariaEmpresa)){
                    $idContaBancariaEmpresa = null;
                }
                
                if(empty($status)){
                    $status = "T";
                }
                
                if(empty($tipoDeposito)){
                    $tipoDeposito = "Q";
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
                
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $depositos = $depositoRn->filtrar($cliente->id, $dataInicial, $dataFinal, $idContaBancariaEmpresa, $tipoDeposito, $status, null, $nresultado, false);
                
                if(sizeof($depositos) > 0){
                    foreach ($depositos as $deposito){
                        $dados = Array(
                            "id" => $deposito->id,
                            "date" => strtotime($deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                            "exchangeAccount" => Array(
                                "code" => $deposito->contaBancariaEmpresa->banco->codigo,
                                "name" => $deposito->contaBancariaEmpresa->banco->nome,
                                "agency" => $deposito->contaBancariaEmpresa->agencia,
                                "account" => $deposito->contaBancariaEmpresa->conta,
                                "type" => $deposito->tipoDeposito
                                ), 
                            "deposit" => number_format($deposito->valorDepositado, 2, ".", ""),
                            "feePorcent" => number_format($deposito->taxaComissao, 2, ".", ""),
                            "feeBrl" => number_format($deposito->valorComissao, 2, ".", ""),
                            "depositCredited" => number_format($deposito->valorCreditado, 2, ".", ","),
                            "status" => $deposito->status
                        );
                        
                        $depositosView[] = $dados;
                        $dados = null;
                    }
                     $httpResponse->addBody("deposits", $depositosView);
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
    
    public function listarDepositosCrypto($params) {

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
                //$status = \Utils\SQLInjection::clean($object->status);
                //$tipoDeposito = \Utils\SQLInjection::clean($object->depositType);                
                $nresultado = \Utils\SQLInjection::clean($object->depth);
                
                $depositosView = Array();
                
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
                $depositos = $contaCorrenteBtcRn->filtrar($cliente->id, $dataInicial, $dataFinal, \Utils\Constantes::ENTRADA, null, "T", $moeda->id, $nresultado, false);
                
                $depositos = $depositos['lista'];
                
                if(sizeof($depositos) > 0){
                    foreach ($depositos as $contaCorrenteBtc){
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
                        
                        $depositosView[] = $dados;
                        $dados = null;
                    }
                     $httpResponse->addBody("deposits", $depositosView);
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
}