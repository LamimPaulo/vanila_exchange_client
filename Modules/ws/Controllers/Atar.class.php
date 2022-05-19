<?php

namespace Modules\ws\Controllers;

class Atar {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function callBack() {
        
        $httpResponse = new \Modules\apiv2\Controllers\HttpResult();
        
        try {
            $json = file_get_contents('php://input');
            $atarApi = new \Atar\AtarApi();
            $atarContasRn = new \Models\Modules\Cadastro\AtarContasRn();
  
            $object = json_decode($json);
            
            $validacao = $atarContasRn->verificarTransacao($object->id);
            
            if (!$validacao) {
               
                $dados = $atarApi->consultarTransferencia($object->id);

                if (!empty($dados)) {

                    $atarContas = new \Models\Modules\Cadastro\AtarContas();

                    $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

                    if (($configuracao->atarIdEmpresa == $dados->target->atarId) && ($configuracao->atarDocumentEmpresa == $dados->target->entity->document)) {

                        $atarContas->retorno = $json;
                        $atarContas->dataCadastro = new \Utils\Data(date('Y-m-d H:i:s'));
                        $atarContas->idClienteAtar = $dados->from->atarId;
                        $atarContas->documentAtar = $dados->from->entity->document;
                        $atarContas->valor = number_format($dados->amount / 100, 2, ".", ""); //Recebe em centavos;
                        $atarContas->idTransacao = $dados->id;
                        $atarContas->tarifa = number_format($configuracao->atarTarifaDeposito, 2, ".", "");
                        $atarContas->taxa = number_format($atarContas->valor * ($configuracao->atarTaxaDeposito / 100), 2, ".", "");
                        $atarContas->taxaPorcentagem = $configuracao->atarTaxaDeposito;
                        $atarContas->valorCreditado = number_format(($atarContas->valor - $atarContas->tarifa - $atarContas->taxa), 2, ".", "");
                        $atarContas->tipo = \Utils\Constantes::ENTRADA;
                       
                        $atarContasRn->salvarTransacao($atarContas);
                    } else {
                        throw new \Exception("Transação não pertence a empresa.");
                    }
                }
            } else {
                
                
                //Transação já registrada - Retornar código 200
                $atarLog = new \Models\Modules\Cadastro\AtarLog();
                $atarLogRn = new \Models\Modules\Cadastro\AtarLogRn();

                $atarLog->response = $json;
                $atarLogRn->salvar($atarLog); 
                
                //$httpResponse->setSuccessful(\Modules\apiv2\Controllers\HTTPResponseCode::$CODE200);
                //throw new \Exception("Transação já registrada.");
            }

            $httpResponse->setSuccessful(\Modules\apiv2\Controllers\HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
         $httpResponse->printResult();
    }
    
    public function checarDepositos($params) {
        
        try {
            
            $dataInicio = \Utils\Post::getData($params, "dataInicio", null, "00:00:00");
            $dataFim = \Utils\Post::getData($params, "dataFim", null, "23:59:59");
            
            $atarApi = new \Atar\AtarApi();
            $atarContasRn = new \Models\Modules\Cadastro\AtarContasRn();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
            if(empty($dataInicio)){
               $dataInicio = new \Utils\Data(date('Y-m-d H:i:s'));
               $dataInicio->subtrair(0, 0, 10); 
            }
            
            if(empty($dataFim)){
               $dataFim = new \Utils\Data(date('Y-m-d H:i:s')); 
            }
            
            $dados = $atarApi->extrato($dataInicio, $dataFim);
            
            $depositos = Array();
            
            $depositos = $dados->data;
            
            foreach ($depositos as $dep) {
                if ($dep->operation == "credit") { // Depósito
                    
                    $validacao = $atarContasRn->verificarTransacao($dep->detail->id);

                    if (!$validacao) {

                        $dados = $atarApi->consultarTransferencia($dep->detail->id);

                        if (!empty($dados)) {

                            $atarContas = new \Models\Modules\Cadastro\AtarContas();

                            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

                            if (($configuracao->atarIdEmpresa == $dados->target->atarId) && ($configuracao->atarDocumentEmpresa == $dados->target->entity->document)) {

                                $atarContas->retorno = json_encode($dados); //Verificar
                                $atarContas->dataCadastro = new \Utils\Data(date('Y-m-d H:i:s'));
                                $atarContas->idClienteAtar = $dados->from->atarId;
                                $atarContas->documentAtar = $dados->from->entity->document;
                                $atarContas->valor = number_format($dados->amount / 100, 2, ".", ""); //Recebe em centavos;
                                $atarContas->idTransacao = $dados->id;
                                $atarContas->tarifa = number_format($configuracao->atarTarifaDeposito, 2, ".", "");
                                $atarContas->taxa = number_format($atarContas->valor * ($configuracao->atarTaxaDeposito / 100), 2, ".", "");
                                $atarContas->taxaPorcentagem = $configuracao->atarTaxaDeposito;
                                $atarContas->valorCreditado = number_format(($atarContas->valor - $atarContas->tarifa - $atarContas->taxa), 2, ".", "");
                                $atarContas->tipo = \Utils\Constantes::ENTRADA;

                                $atarContasRn->salvarTransacao($atarContas);
                            }
                        }                       
                    }
                }
            }

            echo "Verificação executada com sucesso.";
            $json["mensagem"] = "Verificação executada com sucesso.";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function callBackTeste() {

        $httpResponse = new \Modules\apiv2\Controllers\HttpResult();

        try {
            $json = file_get_contents('php://input');

            
            //Transação já registrada - Retornar código 200
            $atarLog = new \Models\Modules\Cadastro\AtarLog();
            $atarLogRn = new \Models\Modules\Cadastro\AtarLogRn();

            $atarLog->response = $json;
            $atarLogRn->salvar($atarLog);


            $httpResponse->addBody("sucesso", "OK");

            $httpResponse->setSuccessful(\Modules\apiv2\Controllers\HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }

        $httpResponse->printResult();
    }

}