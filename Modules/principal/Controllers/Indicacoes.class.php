<?php

namespace Modules\principal\Controllers;

class Indicacoes {
    
    public function index($params) {
        
        try {
            $cliente = \Utils\Geral::getCliente();
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $resgateComissaoRn = new \Models\Modules\Cadastro\ResgateComissaoRn();
            $dataRef = $resgateComissaoRn->getUltimaDataReferencia($cliente);
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $comissaoCartoes = $pedidoCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
            $comissaoMensalidades = $mensalidadeCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $comissaoRecarga = $recargaCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $comissaoBoleto = $boletoClienteRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $comissaoRemessa = $remessaDinheiroRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $transferencias = $resgateComissaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $cartoes = $pedidoCartaoRn->conexao->listar("id_cliente = {$cliente->id} AND ativo = 1 AND cancelado = 0", "numero_cartao");
            
            $params["cartoes"] = $comissaoCartoes;
            $params["mensalidades"] = $comissaoMensalidades;
            $params["recargas"] = $comissaoRecarga;
            $params["boletos"] = $comissaoBoleto;
            $params["remessas"] = $comissaoRemessa;
            $params["configuracao"] = $configuracao;
            $params["pedidosCartoes"] = $cartoes;
            $params["transferencias"] = $transferencias;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("indicacoes", $params);
    }
    
    
    public function getValorResgate($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            
            $resgateComissaoRn = new \Models\Modules\Cadastro\ResgateComissaoRn();
            $dataRef = $resgateComissaoRn->getUltimaDataReferencia($cliente);
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $comissaoCartoes = $pedidoCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
            $comissaoMensalidades = $mensalidadeCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $comissaoRecarga = $recargaCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $comissaoBoleto = $boletoClienteRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $comissaoRemessa = $remessaDinheiroRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            
            $transferencias = $resgateComissaoRn->getRelatorioIndicacoes($cliente->id, $dataRef);
            
            $comissao = $comissaoCartoes["total"] + $comissaoMensalidades["total"] + $comissaoRecarga["comissao"]+$comissaoBoleto["comissao"]+$comissaoRemessa["comissao"];
            foreach ($transferencias as $transferencia) {
                $comissao += $transferencia["valor"];
            }
            
            $json["valor"] = number_format($comissao, 2, ",", ".");
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function resgatar($params) {
        try {
            
            $idPedidoCartao = \Utils\Post::get($params, "idPedidoCartao", 0);
            
             
            
            $resgateComissao = new \Models\Modules\Cadastro\ResgateComissao();
            $resgateComissao->idPedidoCartao = $idPedidoCartao;
            
            $resgateComissaoRn = new \Models\Modules\Cadastro\ResgateComissaoRn();
            $resgateComissaoRn->resgatar($resgateComissao);
            
            $json["mensagem"] = "Comissão resgatada com sucesso!";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function transferir($params) {
        try {
            
            $idCliente = \Utils\Post::get($params, "idCliente", 0);
            
            $resgateComissao = new \Models\Modules\Cadastro\ResgateComissao();
            $resgateComissao->idClienteDestino = $idCliente;
            
            $resgateComissaoRn = new \Models\Modules\Cadastro\ResgateComissaoRn();
            $resgateComissaoRn->transferir($resgateComissao);
            
            $json["mensagem"] = "Comissão transferida com sucesso!";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getClienteByEmail($params) {
        try {
          
            
            $email = \Utils\Post::get($params, "email", null);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($email);
            
            if ($cliente == null) {
                throw new \Exception("Cadastro não localizado");
            }
            
            $json["cliente"] = Array(
                "id" => $cliente->id,
                "nome" => $cliente->nome
            );
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function resgatarSaldo($params) {
        try {
           
            
            $resgateComissao = new \Models\Modules\Cadastro\ResgateComissao();
            
            $resgateComissaoRn = new \Models\Modules\Cadastro\ResgateComissaoRn();
            $resgateComissaoRn->resgatarSaldo($resgateComissao);
            
            $json["mensagem"] = "Comissão resgatada com sucesso!";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}