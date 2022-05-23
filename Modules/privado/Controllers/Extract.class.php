<?php

namespace Modules\privado\Controllers;

class Extract {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }   
    
    public function listarExtrato($params) {
        
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
                $asset = \Utils\SQLInjection::clean($object->asset);          
                $nresultado = \Utils\SQLInjection::clean($object->depth);
                
                $extratoView = Array();
                          
                if(!empty($asset)){
                    $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                    $asset = $moedaRn->getBySimbolo($asset);
                    $idMoeda = $asset->id;
                } else {
                    $idMoeda = "todos";
                }
                
                if(empty($nresultado)){
                    $nresultado = null;
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
                
                $dados = $this->extrato($cliente, $idMoeda, $dataInicial, $dataFinal);
               
                if(sizeof($dados) > 0){
                    
                    $contar = !empty($nresultado) && is_numeric($nresultado);
                    
                    $i = 0;
                    foreach ($dados as $extrato){
                        
                        $extratoView[] = $this->montarObjeto($extrato);
                        $i++;
                        
                        if($contar){
                            if($i == $nresultado){
                                break;
                            }
                        }
                    }
                     $httpResponse->addBody("extract", $extratoView);
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
    
    public function extrato($cliente, $idMoeda = "todos", $dataInicial, $dataFinal) {
        try {
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            
            $listaReais = null;
            $listaCripto = null;
            $listaGeral = Array();
            $real = false;
            $cripto = false;
            $todos = false;
                        
            switch ($idMoeda) {
                case "todos":
                    $real = true;
                    $cripto = true;
                    $todos = true;
                    break;
                
                case $idMoeda == 1:
                    $real = true;                    
                    break;
                
                case $idMoeda > 1:                    
                    $cripto = true;
                    break;
                default:
                    throw new \Exception("Opção inválida.");
                    break;
            }
            
            if($limite == "T"){
                $limite = null;
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("Data inicial não pode ser maior que a data final.");
            }

            if ($real) {
                $listaReais = $contaCorrenteReaisRn->lista(" id_cliente = {$cliente->id} AND data_cadastro <= '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "id DESC", null, null, false, false);
                
                if (sizeof($listaReais) > 0) {
                    $i = 0;
                    $saldo = 0;
                    
                    $listaReais = array_reverse($listaReais);
                    foreach ($listaReais as $reais) {
                        
                        $i++;
                        $tipo = $reais->tipo == \Utils\Constantes::ENTRADA ? 2 : 1;
                        
                        $saldo = $reais->tipo == \Utils\Constantes::ENTRADA ? $reais->valor + $saldo : $saldo - $reais->valor;
                        
                        $reais->saldo = $saldo;
                        
                        if($reais->dataCadastro->maiorIgual($dataInicial)){
                            $ordenar = strtotime($reais->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)) . "-{$i}-{$reais->tipo}";
                            $listaGeral[$ordenar] = $reais;
                        }
                    }
                }
            }

            if($cripto){
                $moeda = "";
                if(!$todos){
                    $moeda = " id_moeda = {$idMoeda} AND ";
                }
                
                $listaCripto = $contaCorrenteBtcRn->lista(" {$moeda} id_cliente = {$cliente->id} AND data_cadastro <= '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "id DESC", null, null, false, true);
                
                if (sizeof($listaCripto) > 0) {
                    $i = 0;
                    $saldo = 0;
                    
                    $arraySaldoMoeda = Array();
                    
                    $listaCripto = array_reverse($listaCripto);
                    foreach ($listaCripto as $criptomoeda) {
                        
                        $i++;
                        $tipo = $criptomoeda->tipo == \Utils\Constantes::ENTRADA ? 2 : 1;
                        
                        
                        if(!isset($arraySaldoMoeda[$criptomoeda->moeda->id])){
                            $arraySaldoMoeda[$criptomoeda->moeda->id] = 0;
                        }
                        
                        $arraySaldoMoeda[$criptomoeda->moeda->id] = $criptomoeda->tipo == \Utils\Constantes::ENTRADA ? $criptomoeda->valor + $arraySaldoMoeda[$criptomoeda->moeda->id] : $arraySaldoMoeda[$criptomoeda->moeda->id] - $criptomoeda->valor;
                        
                        $criptomoeda->saldo = $arraySaldoMoeda[$criptomoeda->moeda->id];
                        
                        if($criptomoeda->dataCadastro->maiorIgual($dataInicial)){
                            $ordenar = strtotime($criptomoeda->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)) . "-{$i}-{$criptomoeda->tipo}";
                            $listaGeral[$ordenar] = $criptomoeda;
                        }
                    }
                }
            }
           
            krsort($listaGeral);
            
            return $listaGeral;
            
        } catch (\Exception $ex) {
            return null;
        }
    }
    
    public function montarObjeto($objeto) {
       
        $dados = null;

        $valor = $objeto->tipo == \Utils\Constantes::SAIDA ? $objeto->valor * -1 : $objeto->valor;

        if ($objeto instanceof \Models\Modules\Cadastro\ContaCorrenteBtc) {

            $dados = Array(
                "asset" => $objeto->moeda->simbolo,
                "date" => strtotime($objeto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                "description" => $objeto->descricao,
                "amount" => number_format($valor, $objeto->moeda->casasDecimais, ".", ""),
                "balance" => number_format($objeto->saldo, $objeto->moeda->casasDecimais, ".", "")
            );
        } else if ($objeto instanceof \Models\Modules\Cadastro\ContaCorrenteReais) {

            $objeto->moeda = \Models\Modules\Cadastro\MoedaRn::get(1);

            $dados = Array(
                "asset" => $objeto->moeda->simbolo,
                "date" => strtotime($objeto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                "description" => $objeto->descricao,
                "amount" => number_format($valor, $objeto->moeda->casasDecimais, ".", ""),
                "balance" => number_format($objeto->saldo, $objeto->moeda->casasDecimais, ".", "")
            );
        }

        return $dados;
        
    }
    
    
}