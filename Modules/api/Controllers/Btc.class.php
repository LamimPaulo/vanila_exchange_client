<?php

namespace Modules\api\Controllers;

class Btc {
    
    public function __construct() {
        
    }
    
    /*public function lancamento($params) {
        
        try {
            
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $transacaoPendenteBtc = new \Models\Modules\Cadastro\TransacaoPendenteBtc();
            $transacaoPendenteBtc->data = \Utils\Post::getData($params, "data", null, "00:00:00");
            $transacaoPendenteBtc->descricao = \Utils\Post::get($params, "descricao", NULL);
            $transacaoPendenteBtc->enderecoBitcoin = \Utils\Post::get($params, "endereco", null);
            $transacaoPendenteBtc->hash = \Utils\Post::getBase64($params, "hash", null);
            $transacaoPendenteBtc->tipo = \Utils\Constantes::ENTRADA;
            $transacaoPendenteBtc->idMoeda = \Utils\Post::get($params, "moeda", 2);
            $transacaoPendenteBtc->valor = \Utils\Post::getNumeric($params, "valor", 0);
            
            
            $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn();
            $transacaoPendenteBtcRn->salvar($transacaoPendenteBtc);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["erro"] = $ex->getCode();
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
    
    
    public function confirmacaodeposito($params) {
        
        try {
            $token = \Utils\Post::get($params, "token") ? \Utils\Post::get($params, "token") : 0;
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $transacaoPendenteBtc = new \Models\Modules\Cadastro\TransacaoPendenteBtc();
            $transacaoPendenteBtc->data = \Utils\Post::getData($params, "data", null, "00:00:00");
            $transacaoPendenteBtc->descricao = \Utils\Post::get($params, "descricao", NULL);
            $transacaoPendenteBtc->enderecoBitcoin = \Utils\Post::get($params, "endereco", null);
            $transacaoPendenteBtc->hash = \Utils\Post::get($params, "hash", null);
            $transacaoPendenteBtc->tipo = \Utils\Constantes::ENTRADA;
            $transacaoPendenteBtc->valor = \Utils\Post::getNumeric($params, "valor", 0);
            $transacaoPendenteBtc->idMoeda = \Utils\Post::get($params, "moeda", 2);
            $jsonTransacaoCore = \Utils\Post::get($params, "json", 2);
            
            $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn();
            $contaCorrenteBtc = $transacaoPendenteBtcRn->validar($transacaoPendenteBtc, $jsonTransacaoCore, $token);
            
            $json["sucesso"] = true;
            $json["id"] = ($contaCorrenteBtc != null ? $contaCorrenteBtc->id : 0);
        } catch (\Exception $ex) {
            $json["erro"] = $ex->getCode();
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
        
    }
    
    
    public function transferenciaspendentes($params) {
        try {
            
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $where = Array();
            $where[] = " executada = 0 ";
            $where[] = " transferencia = 1 ";
            $where[] = " autorizada = 1 ";
            $where[] = " direcao = '".\Utils\Constantes::TRANF_EXTERNA."' ";
            
            
            
            //$where[] = " id_moeda = {$moeda} ";
            $where = implode("AND ", $where);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $result = $contaCorrenteBtcRn->lista($where, "id", NULL, NULL, false, true);
            
            $saqueIcoRn = new \Models\Modules\ICO\SaqueIcoRn();
            $saquesIco = $saqueIcoRn->listar("status = 1", NULL, NULL, NULL, false, true, false);
            
            $lista = Array();
            foreach ($result as $contaCorrenteBtc) {
                
                //$contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
                $lista[] = Array(
                    "controle" => $contaCorrenteBtc->id,
                    "data" => $contaCorrenteBtc->data->formatar(\Utils\Data::FORMATO_PT_BR),
                    "endereco" => $contaCorrenteBtc->enderecoBitcoin,
                    "endereco_envio" => $contaCorrenteBtc->enderecoEnvio,
                    "hash" => base64_encode($contaCorrenteBtc->seed),
                    "cliente" => $contaCorrenteBtc->idCliente,
                    "moeda" => $contaCorrenteBtc->moeda->simbolo,
                    "idMoeda" => $contaCorrenteBtc->moeda->id,
                    "valor" => number_format($contaCorrenteBtc->valor - $contaCorrenteBtc->valorTaxa, $contaCorrenteBtc->moeda->casasDecimais, '.', ''),
                );
                
            }
            
            foreach ($saquesIco as $saqueIco) {

                //$saqueIco = new \Models\Modules\ICO\SaqueIco();
                $lista[] = Array(
                    "controle" => "SAQUEICO-{$saqueIco->id}",
                    "data" => $saqueIco->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR),
                    "endereco" => $saqueIco->wallet,
                    "endereco_envio" => "",
                    "hash" => "",
                    "cliente" => $saqueIco->idCliente,
                    "moeda" => $saqueIco->moedaSaque->simbolo,
                    "idMoeda" => $saqueIco->idMoedaSaque,
                    "valor" => number_format($saqueIco->volumeMoedaSaque, $saqueIco->moedaSaque->casasDecimais, '.', ''),
                );

            }
            
            
            $json["transacoes"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function confirmarTransferencia($params) {
        try {
            
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $controle = \Utils\Post::get($params, "controle", null);
            
            if (is_numeric(strpos($controle, "SAQUEICO-"))) { 
                
                $saqueIco = new \Models\Modules\ICO\SaqueIco();
                $saqueIco->id = str_replace("SAQUEICO-", "", $controle);
                
                $hash = \Utils\Post::get($params, "hash", null);
                $saqueIcoRn = new \Models\Modules\ICO\SaqueIcoRn();
                $saqueIcoRn->finalizarSaque($saqueIco, $hash);
                
            } else {
                $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();

                $contaCorrenteBtc->hash = \Utils\Post::get($params, "hash", null);
                $contaCorrenteBtc->id = $controle;
                $contaCorrenteBtc->data = \Utils\Post::getData($params, "data", null);
                $contaCorrenteBtc->enderecoBitcoin = \Utils\Post::get($params, "endereco", null);
                //$contaCorrenteBtc->idCliente = \Utils\Post::get($params, "cliente", null);
                $contaCorrenteBtc->idMoeda = \Utils\Post::get($params, "moeda", 2);
                $contaCorrenteBtc->valor = \Utils\Post::get($params, "valor", null);

                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $contaCorrenteBtcRn->confirmarTransferencia($contaCorrenteBtc);
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["erro"] = $ex->getCode();
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function transferencia($params) {
        try {
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $cliente = $tokenRn->getClienteByToken($token);
            
            $enderecoBitcoin = \Utils\Post::get($params, "endereco", null);
            $valor = \Utils\Post::getNumeric($params, "valor", null);
            $descricao = \Utils\Post::get($params, "descricao", null);
            $idMoeda = \Utils\Post::get($params, "moeda", null);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrente = $contaCorrenteBtcRn->transferir($cliente, $enderecoBitcoin, $valor, $descricao, $idMoeda, $token);
            
            $json["transacao"] = Array(
                "controle" => $contaCorrente->id,
                "data" => $contaCorrente->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
                "descricao" => $contaCorrente->descricao,
                "endereco" => $contaCorrente->enderecoBitcoin,
                "hash" => $contaCorrente->hash,
                "valor" => number_format($contaCorrente->valor, $contaCorrente->moeda->casasDecimais, ".", ""),
                "comissao" => $contaCorrente->valorTaxa,
                "moeda" => $contaCorrente->moeda->simbolo,
                "status" => ($contaCorrente->executada > 0 ? "Executada" : "Pendente")
            );
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["erro"] = $ex->getCode();
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function statusTransferencia($params) {
        try {
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $cliente = $tokenRn->getClienteByToken($token);
            
            $contaCorrente = new \Models\Modules\Cadastro\ContaCorrenteBtc();
            $contaCorrente->id = \Utils\Post::get($params, "controle", null);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            try {
                $contaCorrenteBtcRn->carregar($contaCorrente, true, true, true);
                if ($contaCorrente->idCliente !== $cliente->id) {
                    throw new \Exception("Você não tem permissão para acessar a transação informada");
                }
            } catch (\Exception $ex) {
                throw new \Exception("Número de controle inválido");
            }
            
            $json["transacao"] = Array(
                "controle" => $contaCorrente->id,
                "data" => $contaCorrente->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
                "descricao" => $contaCorrente->descricao,
                "endereco" => $contaCorrente->enderecoBitcoin,
                "hash" => $contaCorrente->hash,
                "valor" => $contaCorrente->valor,
                "moeda" => $contaCorrente->moeda->simbolo,
                "comissao" => $contaCorrente->valorTaxa,
                "status" => ($contaCorrente->executada > 0 ? "Executada" : "Pendente")
            );
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["erro"] = $ex->getCode();
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function listarTransferencias($params) {
        try {
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $dataInicial = \Utils\Post::getData($params, "dtinicio", null);
            $dataFinal = \Utils\Post::getData($params, "dtfim", null);
            $moeda = \Utils\Post::get($params, "moeda", 0);
            
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception("Data inicial inválida");
            }
            
            if (!isset($dataFinal->data) || $dataFinal->data == null) {
                throw new \Exception("Data final inválida");
            }
            
            $cliente = $tokenRn->getClienteByToken($token);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            
            $where = Array();
            $where[] = " id_cliente = {$cliente->id} ";
            $where[] = " transferencia = 1 ";
            $where[] = " direcao = 'E' ";
            $where[] = " executada = 0  ";
            $where[] = " autorizada = 1  ";
            
            if ($moeda > 0) {
                $where[] = " id_moeda = {$moeda} ";
            }
            
            $where = implode(" AND ", $where);
            
            $result = $contaCorrenteBtcRn->lista($where, "data DESC", null, null, true, true);
           
            $lista = Array();
            
            foreach ($result as $contaCorrente) {
                
                $lista[] = Array(
                    "controle" => $contaCorrente->id,
                    "data" => $contaCorrente->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
                    "descricao" => $contaCorrente->descricao,
                    "endereco" => $contaCorrente->enderecoBitcoin,
                    "hash" => $contaCorrente->hash,
                    "valor" => $contaCorrente->valor,
                    "moeda" => $contaCorrente->moeda->simbolo,
                    "comissao" => $contaCorrente->valorTaxa,
                    "status" => ($contaCorrente->executada > 0 ? "Executada" : "Pendente")
                );
            }
            
            $json["transacoes"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["erro"] = $ex->getCode();
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function sms($params) {
        try {
            $msg = "Exchange Servidor: ". \Utils\Post::get($params, "msg", "Alerta Bot Parado!");
            $cel1 = "11976066557";
            $cel2 = "11996531000";
            $cel3 = "11996574471";
            $cel4 = "11961219831";
            

            $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
            $api->EnviaSMS("55{$cel1}", $msg);
            $api->EnviaSMS("55{$cel2}", $msg);
            $api->EnviaSMS("55{$cel3}", $msg);
            $api->EnviaSMS("55{$cel4}", $msg);

            
        } catch (\Exception $ex) {
        }
       print "{sucesso: true}";
    }*/
}