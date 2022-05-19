<?php

namespace Modules\ws\Controllers;

class LaraBoleto {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function campainha() {
        
        $httpResponse = new \Modules\apiv2\Controllers\HttpResult();
        
        try {
            $json = file_get_contents('php://input');
            
            $laraLog = new \Models\Modules\Cadastro\LaraLog();
            $laraLogRn = new \Models\Modules\Cadastro\LaraLogRn();

            $laraLog->response = $json;
            $laraLog->categoria = "Callback Boleto";
            
            $laraLogRn->salvar($laraLog); 
            
            //Transforma em objeto
            $objectCampainha = json_decode($json);
            
            //Verifica se id do depósito é válido
            $deposito = null;
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositos = $depositoRn->listar(" id = {$objectCampainha->dadosConf->identInterno} ");
            
            if(sizeof($depositos) > 0){
                $deposito = current($depositos);
            } else {
                throw new \Exception("Depópsito não encontrado.");
            }
            
            //Consulta deposito na Lara
            if(!empty($deposito)){
                $boletosLaraApi = new \BoletosLara\BoletosLara();
                $object = $boletosLaraApi->consultarBoleto($objectCampainha);
                
                if(!empty($object)){
                 
                    switch ($object->data->situacao) {
                        case 0: //Pendente
                            break;
                        case 1: //Autorizado
                            //Atualizar Deposito
                            $this->atualizarDeposito($deposito, $object);
                            break;
                        case 2: //Erro
                        case 3: //Recusado Banco
                        case 4: //Cancelado
                        case 5: //Baixa Manual
                            $this->cancelarBoleto($deposito);
                            break;
                        case 6: //pago
                            $this->aprovarDeposito($deposito);
                            break;
                        case 7: //Bloqueado
                        case 8: //Bloqueio Juridico
                        case 9: //Bloquedio administrativo
                            $this->cancelarBoleto($deposito);   
                            break;
                        default:
                            throw new \Exception("Falha verificar boleto.");
                    }
                    
                }
            }

            $httpResponse->setSuccessful(\Modules\apiv2\Controllers\HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
         $httpResponse->printResult();
    }
    
    
    private function aprovarDeposito($deposito) {
        try {
            if (!empty($deposito)) {
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $depositoRn->aprovar($deposito);
            }
        } catch (Exception $ex) {
            throw new \Exception("Falha aprovar boleto.");
        }
    }
    
    private function atualizarDeposito($deposito, $object) {
        try {
            if (!empty($deposito)) {
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $depositoRn->conexao->update(
                        Array(
                    "status_gateway" => \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO,
                    "motivo_cancelamento" => null,
                    "data_cancelamento" => null,
                    "link_gateway" => $object->data->dadosCob->urlPDF,
                    "barcode_gateway" => $object->data->dadosCob->linhaDig,       
                    ), Array("id" => $deposito->id));
            }
            
            $dados[1] = "R$ " . number_format($deposito->valorDepositado, 2, ",", ".");
            
            \Email\EmailMain::send($deposito->cliente, 9, null, $dados);
            
        } catch (Exception $ex) {
            throw new \Exception("Falha atualizar boleto.");
        }
    }
    
    private function cancelarBoleto($deposito) {
        try{

            $depositosRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositosRn->conexao->update(Array("status" => \Utils\Constantes::STATUS_DEPOSITO_CANCELADO, "data_cancelamento" => date("Y-m-d H:i:s"), "id_usuario" => 1483296812, "motivo_cancelamento" => "Boleto não pago", "status_gateway" => \Utils\Constantes::STATUS_DEPOSITO_CANCELADO), Array("id" => $deposito->id));

        } catch (Exception $ex) {
            throw new \Exception("Falha cancelar boleto.");
        }
        
        
    }
    
    
    
//    public function cancelarBoleto($deposito) {
//        
//        $dataAtual = new \Utils\Data(date("Y-m-d H:i:s"));
//        $depositosRn = new \Models\Modules\Cadastro\DepositoRn();
//        $depositos = $depositosRn->listar(" tipo_deposito = 'G' AND status = 'P' AND data_vencimento_gateway IS NOT NULL AND data_vencimento_gateway < '{$dataAtual->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "id ASC");
//        
//        if(sizeof($depositos) > 0){
//            foreach ($depositos as $deposito){
//                
////                if($this->gerenciaNetCancelar($deposito)){
////                    $depositosRn->conexao->update(Array("status" => \Utils\Constantes::STATUS_DEPOSITO_CANCELADO, "data_cancelamento" => date("Y-m-d H:i:s"), "id_usuario" => 1483296812, "motivo_cancelamento" => "Boleto não pago"), Array("id" => $deposito->id));
////                } 
////                usleep(500000);
//            }
//        } 
//        
//        echo "Depositos cancelados";
//    }
    
}