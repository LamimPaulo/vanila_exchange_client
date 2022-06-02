<?php

namespace Modules\ws\Controllers;

class GerenciaNet {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function notification($params) {
        
        try {
            $token = \Utils\Post::get($params, "notification", null);
 
            $dados = [
              'token' => $token
            ];
            
            if(AMBIENTE == "producao"){
                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$options);
            } else {
                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$optionsDev);
            }
            $chargeNotification = $api->getNotification($dados, []);
            
            $i = count($chargeNotification["data"]);
            // Pega o último Object chargeStatus
            $ultimoStatus = $chargeNotification["data"][$i-1];
            // Acessando o array Status
            $idBoleto = $ultimoStatus["custom_id"];
            $status = $ultimoStatus["status"];
            // Obtendo o ID da transação    
            $charge_id = $ultimoStatus["identifiers"]["charge_id"];
            // Obtendo a String do status atual
            $statusAtual = $status["current"];
            
            //$statusAtual = "paid";
            
            if ($statusAtual == "paid" || $statusAtual == "settled") {
                $this->aprovarDeposito($idBoleto);
            }
            
            
            header("HTTP/1.1 200 Success");
        } catch (\Gerencianet\Exception\GerencianetException $ex) {
            $mensagem = "$ex->code - $ex->error - ";
            if (is_array($ex->errorDescription)) {
                foreach ($ex->errorDescription as $value) {
                    $mensagem .= $value . ".";
                }
            } else {
                $mensagem .= $ex->errorDescription . ".";
            }
            
            header("HTTP/1.1 400 {$mensagem}");
        }  catch (\Exception $ex) {
            $mensagem = \Utils\Excecao::mensagem($ex);
            header("HTTP/1.1 400 {$mensagem}");
        }
        
        header('Content-type: text/html; charset=UTF-8');
    }
    
    private function aprovarDeposito($idDeposito) {
        try {
            if (!empty($idDeposito)) {
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $deposito = new \Models\Modules\Cadastro\Deposito(Array("id" => $idDeposito));

                $depositoRn->conexao->carregar($deposito);

                $depositoRn->aprovar($deposito);
                $depositoRn->conexao->update(Array("status_gateway" => $deposito->statusGateway, "motivo_cancelamento" => null, "data_cancelamento" => null), Array("id" => $deposito->id));
            }
        } catch (Exception $ex) {
            
        }
    }
    
    public function cancelarBoletos() {
        
        $dataAtual = new \Utils\Data(date("Y-m-d H:i:s"));
        $depositosRn = new \Models\Modules\Cadastro\DepositoRn();
        $depositos = $depositosRn->listar(" tipo_deposito = 'G' AND status = 'P' AND data_vencimento_gateway IS NOT NULL AND data_vencimento_gateway < '{$dataAtual->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "id ASC");
        
        if(sizeof($depositos) > 0){
            foreach ($depositos as $deposito){
                
                if($this->gerenciaNetCancelar($deposito)){
                    $depositosRn->conexao->update(Array("status" => \Utils\Constantes::STATUS_DEPOSITO_CANCELADO, "data_cancelamento" => date("Y-m-d H:i:s"), "id_usuario" => 1483296812, "motivo_cancelamento" => "Boleto não pago"), Array("id" => $deposito->id));
                } 
                usleep(500000);
            }
        } 
        
        echo "Depositos cancelados";
    }
    
    private function gerenciaNetCancelar($deposito) {

        try {
            if(AMBIENTE == "producao"){
                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$options);
            } else {
                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$optionsDev);
            }
            
            $params = [
                'id' => $deposito->idGateway
              ];
            
            $charge = $api->cancelCharge($params, []);
            
            if($charge["code"] == 200){
                return true;
            } else {
                return false;
            }

        } catch (\Gerencianet\Exception\GerencianetException $e) {
            $mensagem = "$ex->code - $ex->error - ";
            if (is_array($ex->errorDescription)) {
                foreach ($ex->errorDescription as $value) {
                    $mensagem .= $value . ".";
                }
            } else {
                $mensagem .= $ex->errorDescription . ".";
            }
            header("HTTP/1.1 400 {$mensagem}");
        } catch (\Exception $e) {
            print_r($e->getMessage());
            header("HTTP/1.1 400 {$mensagem}");
        }
        
        header('Content-type: text/html; charset=UTF-8');
    }
    
    public function gerenciaNetConsultar($params) {
       
        try {
            $idDeposito = \Utils\Get::get($params, 0, null);
            
            if(AMBIENTE == "producao"){
                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$options);
            } else {
                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$optionsDev);
            }
            
            if (!empty($idDeposito)) {
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $deposito = new \Models\Modules\Cadastro\Deposito(Array("id" => $idDeposito));

                $depositoRn->conexao->carregar($deposito);
                
                $dados = [
                    'id' => $deposito->idGateway
                ];

                $charge = $api->detailCharge($dados, []);

                $boleto = $charge["data"];

                if ($boleto["status"] == "paid" && $deposito->idGateway == $boleto["charge_id"] && $boleto["custom_id"] == $deposito->id) {
                    $this->aprovarDeposito($deposito->id);
                    print_r($boleto);
                    echo "Boleto Aprovado.";
                } else {
                    print_r($boleto);
                    echo "Boleto Não Aprovado.";
                }
            }
        } catch (\Gerencianet\Exception\GerencianetException $e) {
            $mensagem = "$ex->code - $ex->error - ";
            if (is_array($ex->errorDescription)) {
                foreach ($ex->errorDescription as $value) {
                    $mensagem .= $value . ".";
                }
            } else {
                $mensagem .= $ex->errorDescription . ".";
            }
            header("HTTP/1.1 400 {$mensagem}");
        } catch (\Exception $e) {
            print_r($e->getMessage());
            header("HTTP/1.1 400 {$mensagem}");
        }
        
        header('Content-type: text/html; charset=UTF-8');
    }
    
    
    
//    private function gerenciaNetConsultarBoleto($params) {
//
//        try {
//            if(AMBIENTE == "producao"){
//                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$options);
//            } else {
//                $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$optionsDev);
//            }
//            
//            
//            $idGateway = \Utils\Get::get($params, 0, null);
//            
//            /*if (empty($idGateway)) {
//
//                $dataAtual = new \Utils\Data(date("Y-m-d H:i:s"));
//                $dataAtual->somar(0, 0, 2);
//
//                $depositos = $depositosRn->listar(" tipo_deposito = 'G' AND status = 'P' AND data_vencimento_gateway IS NOT NULL AND data_vencimento_gateway < '{$dataAtual->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "id ASC");
//
//                if (sizeof($depositos) > 0) {
//                    foreach ($depositos as $deposito) {
//
//                        if ($this->gerenciaNetCancelar($deposito)) {
//                            $depositosRn->conexao->update(Array("status" => \Utils\Constantes::STATUS_DEPOSITO_CANCELADO, "data_cancelamento" => date("Y-m-d H:i:s"), "id_usuario" => 1483296812, "motivo_cancelamento" => "Boleto não pago"), Array("id" => $deposito->id));
//                        }
//                        usleep(500000);
//                    }
//                }
//            }*/
//
//
//
//            $params = [
//                'id' => $deposito->idGateway
//              ];
//            
//            $charge = $api->cancelCharge($params, []);
//            
//            if($charge["code"] == 200){
//                return true;
//            } else {
//                return false;
//            }
//
//        } catch (\Gerencianet\Exception\GerencianetException $e) {
//            $mensagem = "$ex->code - $ex->error - ";
//            if (is_array($ex->errorDescription)) {
//                foreach ($ex->errorDescription as $value) {
//                    $mensagem .= $value . ".";
//                }
//            } else {
//                $mensagem .= $ex->errorDescription . ".";
//            }
//            header("HTTP/1.1 400 {$mensagem}");
//        } catch (\Exception $e) {
//            print_r($e->getMessage());
//            header("HTTP/1.1 400 {$mensagem}");
//        }
//        
//        header('Content-type: text/html; charset=UTF-8');
//    }

}