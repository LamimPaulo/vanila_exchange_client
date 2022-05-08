<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade RecargaCartao
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class RecargaCartaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new RecargaCartao());
        } else {
            $this->conexao = new GenericModel($adapter, new RecargaCartao());
        }
    }
    
    public function salvar(RecargaCartao &$recargaCartao) {
        
        if ($recargaCartao->id > 0) {
            $aux = new RecargaCartao(Array("id" => $recargaCartao->id));
            $this->conexao->carregar($aux);
            
            
            $recargaCartao->dataRecargaFinalizada = $aux->dataRecargaFinalizada;
            $recargaCartao->dataPedido = $aux->dataPedido;
            $recargaCartao->idClienteRecarga = $aux->idClienteRecarga;
        } else {
            if ($recargaCartao->idInvoice != null || empty($recargaCartao->status)) { 
                $recargaCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO;
            } 
            $recargaCartao->dataRecargaFinalizada = null;
            $recargaCartao->dataPedido = new \Utils\Data(date("d/m/Y H:i:s"));
            $cliente = \Utils\Geral::getCliente();
            if ($cliente != null) {
                $recargaCartao->idClienteRecarga = $cliente->id;
            }
        }
        
        if (empty($recargaCartao->address)) {
            //throw new \Exception("É necessário informar o endereço de pagamento");
        }
        
        if (!isset($recargaCartao->dataExpiracaoInvoice->data) || $recargaCartao->dataExpiracaoInvoice->data == null) {
            //throw new \Exception("Data da expiração da invoice inválida.");
        }
        
        if (!$recargaCartao->idInvoice > 0) {
            //throw new \Exception("A identificação da invoice deve ser informada");
        }
        
        if (!$recargaCartao->idPedidoCartao > 0) {
            //throw new \Exception("A identificação do cartão deve ser informada");
        }
        
        if (!is_numeric($recargaCartao->valorReal) || !$recargaCartao->valorReal>0) {
            throw new \Exception("Valor total real inválido");
        }
        
        if (!is_numeric($recargaCartao->valorBtc) || !$recargaCartao->valorBtc>0) {
            //throw new \Exception("Valor total BTC inválido");
        }
        
        $status = Array(
            \Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO,
            \Utils\Constantes::STATUS_RECARGA_CARTAO_CANCELADO,
            \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO,
            \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO
        );
        
        if (!in_array($recargaCartao->status, $status)) {
            throw new \Exception("Status inválido");
        }
        
        $this->conexao->salvar($recargaCartao);
    }
    
    public function finalizar(RecargaCartao &$recargaCartao) {
        try {
            $this->conexao->carregar($recargaCartao);
        } catch (\Exception $ex) {
            throw new \Exception("Pedido de recarga não localizado no sistema");
        }
        
        if ($recargaCartao->status == \Utils\Constantes::STATUS_RECARGA_CARTAO_CANCELADO) {
            throw new \Exception("Não é possível finalizar uma recarga cancelada");
        }
        if ($recargaCartao->status == \Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO) {
            throw new \Exception("Não é possível finalizar uma recarga aguardando pagamento");
        }
        \Modules\invoices\Controllers\Cards::executaRecargaVisa($recargaCartao);
        
        $this->conexao->carregar($recargaCartao);
        /*$recargaCartao->status = \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO;
        $recargaCartao->dataRecargaFinalizada = new \Utils\Data(date("d/m/Y H:i:s"));*/
        
        /*$this->conexao->update(
                Array(
                    "status" => $recargaCartao->status,
                    "data_recarga_finalizada" => $recargaCartao->dataRecargaFinalizada->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                ),
                Array(
                    "id" => $recargaCartao->id
                )
            );*/
    }
    
    
    
    public function getByIdInvoice($idInvoice) {
        $result = $this->conexao->select(Array("id_invoice" => $idInvoice));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public function filtrar(\Utils\Data $dataInicial, \Utils\Data $dataFinal, $status = "T", $idPedidoCartao = 0, $filtro = null) {
        
        $where = Array();
            
        $where[] = " r.data_pedido BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";

        if ($status != "T") {
            $where[] = " r.status = '{$status}' ";
        }

        
        if (!empty($filtro)) {
            $where[] = " ( (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.email) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.cidade) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.celular) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.rg) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.nome_cartao) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.nome_cartao) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.numero_cartao) LIKE LOWER('%{$filtro}%')) ) ";
        }
        
        if ($idPedidoCartao > 0) {
            $where[] = " r.id_pedido_cartao = {$idPedidoCartao} ";
        } else {
            if (\Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $where[] = " (p.id_cliente = {$cliente->id} OR r.id_cliente_recarga = {$cliente->id}) ";
            }
        }
        

        $where = (sizeof($where) > 0 ? implode(" AND ", $where) : "");
        
        $query = "SELECT r.* FROM recargas_cartoes r "
                . "INNER JOIN pedidos_cartoes p ON (r.id_pedido_cartao = p.id) "
                . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                . "WHERE {$where} ORDER BY r.data_pedido DESC;"; 
        
        $lista = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $recargaCartao = new RecargaCartao($dados);
            $lista[] = $recargaCartao;
        }
        
        return $lista;
    }
    
    
    public function getCartoesRecargaParaTerceiros() {
        $cliente = \Utils\Geral::getCliente();
        $cartoes = Array();
        if ($cliente != null) {
            $pedidoCartaoRn = new PedidoCartaoRn();
            $query = "SELECT DISTINCT(p.id), p.* "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN recargas_cartoes r ON (r.id_pedido_cartao = p.id) "
                    . "WHERE r.id_cliente_recarga = {$cliente->id} AND p.id_cliente != {$cliente->id} ";
                    
            $result = $this->conexao->adapter->query($query)->execute();
            foreach ($result as $dados) {
                $pedidoCartao = new PedidoCartao($dados);
                $pedidoCartaoRn->carregar($pedidoCartao, false, true);
                $cartoes[] = $pedidoCartao;
            }
            
        }
        return $cartoes;
    }
    
    
    
    public function getRelatorioIndicacoes($idReferencia, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = "";
        if ($dataInicial != null) {
            if ($dataFinal != null) {
                $where = " AND r.data_pagamento BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            } else {
                $where = " AND r.data_pagamento >= '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            }
        }
        
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $configuracao->percentualComissaoRecarga1 = ($configuracao->percentualComissaoRecarga1 / 100);
        $configuracao->percentualComissaoRecarga2 = ($configuracao->percentualComissaoRecarga2 / 100);
        $configuracao->percentualComissaoRecarga4 = ($configuracao->percentualComissaoRecarga4 / 100);
        $configuracao->percentualComissaoRecarga5 = ($configuracao->percentualComissaoRecarga5 / 100);
        $configuracao->percentualComissaoRecarga3 = ($configuracao->percentualComissaoRecarga3 / 100);
        
        $queryLv1 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "sum(r.valor_real) AS valor "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN recargas_cartoes r ON (p.id = r.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia = {$idReferencia} AND "
                    . "r.status IN ('".\Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO."', '".\Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO."') {$where} "
                    . "GROUP BY c.id, c.nome;";
                    
        $mmn = Array();
        $resultLv1 = $this->conexao->adapter->query($queryLv1)->execute();
        
        $valorGeral = 0;
        $comissaoGeral = 0;
        
        $idsLv1 = Array();
        $clientesLv1 = Array();
        $valorLv1 = 0;
        $comissaoLv1 = 0;
        foreach ($resultLv1 as $dadosLv1) {
            $clientesLv1[] = Array("id" => $dadosLv1["id"], "nome" => $dadosLv1["nome"], "valor" => $dadosLv1["valor"]);
            $idsLv1[] = $dadosLv1["id"];
            $comissaoLv1 +=  ($dadosLv1["valor"] * $configuracao->percentualComissaoRecarga1);
            $valorLv1 += $dadosLv1["valor"];
            $valorGeral += $dadosLv1["valor"];
            $comissaoGeral += ($dadosLv1["valor"] * $configuracao->percentualComissaoRecarga1);
        }
        
        $mmn["lv1"] = Array(
            "clientes" => $clientesLv1,
            "comissao" => $comissaoLv1,
            "valor" => $valorLv1
        );
        
        $idsLv2 = Array();
        $clientesLv2 = Array();
        $valorLv2 = 0;
        $comissaoLv2 = 0;
        if (sizeof($idsLv1) > 0) {
            $queryLv2 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "sum(r.valor_real) AS valor "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN recargas_cartoes r ON (p.id = r.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv1).") AND "
                    . "r.status IN ('".\Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO."', '".\Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO."') "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv2 = $this->conexao->adapter->query($queryLv2)->execute();
            foreach ($resultLv2 as $dadosLv2) {
                $clientesLv2[] = Array("id" => $dadosLv2["id"], "nome" => $dadosLv2["nome"], "valor" => $dadosLv2["valor"]);
                $idsLv2[] = $dadosLv2["id"];
                $comissaoLv2 +=  ($dadosLv2["valor"] * $configuracao->percentualComissaoRecarga2);
                $valorLv2 += $dadosLv2["valor"];
                $valorGeral += $dadosLv2["valor"];
                $comissaoGeral += ($dadosLv2["valor"] * $configuracao->percentualComissaoRecarga2);
            }
        }
        
        
        $mmn["lv2"] = Array(
            "clientes" => $clientesLv2,
            "comissao" => $comissaoLv2,
            "valor" => $valorLv2
        );
        
        $idsLv3 = Array();
        $clientesLv3 = Array();
        $valorLv3 = 0;
        $comissaoLv3 = 0;
        if (sizeof($idsLv2) > 0) {
            $queryLv3 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "sum(r.valor_real) AS valor "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN recargas_cartoes r ON (p.id = r.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv2).") AND "
                    . "r.status IN ('".\Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO."', '".\Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO."') "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv3 = $this->conexao->adapter->query($queryLv3)->execute();
            foreach ($resultLv3 as $dadosLv3) {
                $clientesLv3[] = Array("id" => $dadosLv3["id"], "nome" => $dadosLv3["nome"], "valor" => $dadosLv3["valor"]);
                $idsLv3[] = $dadosLv3["id"];
                $comissaoLv3 +=  ($dadosLv3["valor"] * $configuracao->percentualComissaoRecarga3);
                $valorLv3 += $dadosLv3["valor"];
                $valorGeral += $dadosLv3["valor"];
                $comissaoGeral += ($dadosLv3["valor"] * $configuracao->percentualComissaoRecarga3);
            }
        }
        
        $mmn["lv3"] = Array(
            "clientes" => $clientesLv3,
            "comissao" => $comissaoLv3,
            "valor" => $valorLv3
        );
        
        
        $idsLv4 = Array();
        $clientesLv4 = Array();
        $valorLv4 = 0;
        $comissaoLv4 = 0;
        if (sizeof($idsLv3) > 0) {
            $queryLv4 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "sum(r.valor_real) AS valor "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN recargas_cartoes r ON (p.id = r.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv3).") AND "
                    . "r.status IN ('".\Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO."', '".\Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO."') "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv4 = $this->conexao->adapter->query($queryLv4)->execute();
            foreach ($resultLv4 as $dadosLv4) {
                $clientesLv4[] = Array("id" => $dadosLv4["id"], "nome" => $dadosLv4["nome"], "valor" => $dadosLv4["valor"]);
                $idsLv4[] = $dadosLv4["id"];
                $comissaoLv4 +=  ($dadosLv4["valor"] * $configuracao->percentualComissaoRecarga4);
                $valorLv4 += $dadosLv4["valor"];
                $valorGeral += $dadosLv4["valor"];
                $comissaoGeral += ($dadosLv4["valor"] * $configuracao->percentualComissaoRecarga4);
            }
        }
        
        $mmn["lv4"] = Array(
            "clientes" => $clientesLv4,
            "comissao" => $comissaoLv4,
            "valor" => $valorLv4
        );
        
        
        
        $clientesLv5 = Array();
        $comissaoLv5 = 0;
        $valorLv5 = 0;
        if (sizeof($idsLv4) > 0) {
            $queryLv5 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "sum(r.valor_real) AS valor "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN recargas_cartoes r ON (p.id = r.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv4).") AND "
                    . "r.status IN ('".\Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO."', '".\Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO."') "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv5 = $this->conexao->adapter->query($queryLv5)->execute();
            foreach ($resultLv5 as $dadosLv5) {
                $clientesLv5[] = Array("id" => $dadosLv5["id"], "nome" => $dadosLv5["nome"], "valor" => $dadosLv5["valor"]);
                $comissaoLv5 +=  ($dadosLv5["valor"] * $configuracao->percentualComissaoRecarga5);
                $valorLv5 += $dadosLv5["valor"];
                $valorGeral += $dadosLv5["valor"];
                $comissaoGeral += ($dadosLv5["valor"] * $configuracao->percentualComissaoRecarga5);
            }
        }
        
        $mmn["lv5"] = Array(
            "clientes" => $clientesLv5,
            "comissao" => $comissaoLv5,
            "valor" => $valorLv5
        );
        
        $mmn["comissao"] = $comissaoGeral;
        $mmn["valor"] = $valorGeral;
        
        return $mmn;
    }
    
    
    public function criarRecarga(PedidoCartao $pedidoCartao, $valor) {
        try {
            $cliente = new Cliente(Array("id" => $pedidoCartao->idCliente));
            
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn();
            $contaCorrente = $contaCorrenteReaisRn->debitarDoSaldo($cliente, $valor, "Recarga do cartão {$pedidoCartao->numeroCartao}", false);
            
            $recargaCartao = new \Models\Modules\Cadastro\RecargaCartao();
            $recargaCartao->id = 0;
            $recargaCartao->valorReal = $valor;
            $recargaCartao->idPedidoCartao = $pedidoCartao->id;
            $recargaCartao->status = \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO;
            
            if ($contaCorrente != null) {
                $recargaCartao->address = null;
                $recargaCartao->dataExpiracaoInvoice = null;
                $recargaCartao->valorBtc = null;
                $recargaCartao->idInvoice = null;
            } else {
                $orders = new \BitcoinToYou\Orders();
                $order = $orders->create($valor, URLBASE_CLIENT . \BitcoinToYou\Access::DEFAULT_REDIRECT_CALLBACK);
                
                $recargaCartao->address = $order->DigitalCurrencyAddress;
                $recargaCartao->dataExpiracaoInvoice = new \Utils\Data(str_replace("T", " ", $order->ExpirationDate));
                $recargaCartao->valorBtc = $order->DigitalCurrencyAmount;
                $recargaCartao->idInvoice = $order->InvoiceId;
            }
            
            $this->salvar($recargaCartao);
            
            return $recargaCartao;
        } catch (\Exception $ex) {
            if (isset($contaCorrente) && $contaCorrente->id > 0) {
                $contaCorrenteReaisRn->excluir($contaCorrente);
            }
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        return null;
    }
    
    
    public function getQuantidadePorStatus() {
        
        $query = " SELECT status, COUNT(*) As qtd FROM recargas_cartoes GROUP BY status";
        
        $dados = $this->conexao->adapter->query($query)->execute();
        
        $aguardando = 0;
        $canceladas = 0;
        $finalizadas = 0;
        $pagos = 0;
        
        foreach ($dados as $d) {
            switch ($d["status"]) {
                case \Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO:
                    $aguardando += $d["qtd"];
                    break;
                case \Utils\Constantes::STATUS_RECARGA_CARTAO_CANCELADO:
                    $canceladas += $d["qtd"];
                    break;
                case \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO:
                    $finalizadas += $d["qtd"];
                    break;
                case \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO:
                    $pagos += $d["qtd"];
                    break;

                default:
                    break;
            }
        }
        
        return Array("aguardando" => $aguardando, "canceladas" => $canceladas, "pagos" => $pagos, "finalizadas" => $finalizadas);
        
    }
}

?>